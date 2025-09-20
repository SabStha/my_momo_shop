<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Referral;
use App\Models\Creator;
use Illuminate\Support\Facades\Session;
use App\Events\OrderPlaced;
use App\Models\PosAccessLog;
use Illuminate\Support\Facades\Auth;
use App\Services\CreatorPointsService;
use App\Services\ReferralService;
use App\Services\CartCalculationService;

class OrderController extends Controller
{
    protected $creatorPointsService;
    protected $cartCalculationService;

    public function __construct(CreatorPointsService $creatorPointsService, CartCalculationService $cartCalculationService)
    {
        $this->creatorPointsService = $creatorPointsService;
        $this->cartCalculationService = $cartCalculationService;
        $this->middleware('auth')->except(['store']);
    }

    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(
                Order::with('items.product')
                    ->latest()
                    ->paginate(10)
            );
        }

        // Get inline orders (online orders)
        $inlineOrders = Order::where('type', 'online')
            ->where('status', '!=', 'completed')
            ->with(['items.product', 'user'])
            ->latest()
            ->get();

        // Get POS orders (dine-in, takeaway)
        $posOrders = Order::whereIn('type', ['dine_in', 'takeaway'])
            ->where('status', '!=', 'completed')
            ->with(['items.product', 'user', 'table'])
            ->latest()
            ->get();

        // Get order history (completed orders)
        $orderHistory = Order::where('status', 'completed')
            ->with(['items.product', 'user', 'table'])
            ->latest()
            ->paginate(20);

        return view('orders.index', compact('inlineOrders', 'posOrders', 'orderHistory'));
    }

    public function show(Order $order)
    {
        // Check if the user is authorized to view this order
        if (auth()->user()->id !== $order->user_id && !auth()->user()->hasRole(['admin', 'cashier'])) {
            abort(403);
        }

        // Load the order items with their products
        $order->load(['items.product', 'user']);

        // Determine which view to use based on the route
        $view = request()->route()->getName() === 'my-account.orders.show' 
            ? 'user.my-account.order-details'
            : 'orders.show';

        return view($view, compact('order'));
    }

    public function pay(Request $request, Order $order)
    {
        $data = $request->validate([
            'amount_received' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,qr',
        ]);

        $total = $order->grand_total;
        $change = $data['amount_received'] - $total;

        $order->update([
            'payment_method' => $data['payment_method'],
            'amount_received' => $data['amount_received'],
            'change' => $change,
            'payment_status' => 'paid',
            'status' => 'completed',
            'paid_by' => $request->input('paid_by'),
        ]);

        // Set table to available if dine-in order
        if ((Str::contains($order->type, 'dine') || $order->type === 'dine-in' || $order->type === 'dine_in') && $order->table_id) {
            $order->table()->update(['status' => 'available']);
        }

        // Process referral if exists
        $referral = Referral::where('referred_id', $order->user->id)
            ->where('status', 'registered')
            ->first();

            if ($referral) {
            $referralService = new ReferralService();
            $referralService->processOrder($order->user, $referral, $order);
                }

        // Fire OrderPlaced event
        event(new OrderPlaced($order));

        // Log the payment
        PosAccessLog::create([
            'user_id' => Auth::id(),
            'access_type' => 'payment_manager',
            'action' => 'payment',
            'details' => [
                'order_id' => $order->id,
                'amount' => $order->total_amount,
                'payment_method' => $request->payment_method
            ],
            'ip_address' => $request->ip()
        ]);

        // Return JSON for AJAX requests
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true, 
                'message' => 'Payment processed!',
                'order' => $order->load(['items.product', 'user'])
            ]);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Payment processed!');
    }

    public function receipt(Order $order)
    {
        $order->load(['items', 'table', 'user']);
        return view('orders.receipt', compact('order'));
    }

    public function kitchenReceipt(Order $order)
    {
        $order->load(['items', 'table']);
        return view('orders.kitchen-receipt', compact('order'));
    }

    public function report(Request $request)
    {
        $orders = Order::whereBetween('created_at', [
            $request->date_from ?? now()->startOfMonth(),
            $request->date_to ?? now()->endOfMonth()
        ])->get();

        $totalSales = $orders->sum('grand_total');
        $totalPaid = $orders->where('payment_status', 'paid')->sum('grand_total');

        return view('orders.report', compact('orders', 'totalSales', 'totalPaid'));
    }

    public function store(Request $request)
    {
        // Parse input and cast to proper types
        $branchId = (int) $request->input('branch_id', 1);
        $clientItems = $request->input('items', []);
        
        // Validate the request
        $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'city' => 'required|string|max:255',
                'ward_number' => 'nullable|string|max:50',
                'area_locality' => 'nullable|string|max:255',
                'building_name' => 'nullable|string|max:255',
                'detailed_directions' => 'nullable|string',
                'payment_method' => 'required|in:cash,card,wallet,fonepay,esewa,khalti',
                'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.type' => 'nullable|string|in:product,bulk',
                'total' => 'required|numeric|min:0',
                'applied_offer' => 'nullable|string',
                'gps_location' => 'nullable|array',
            ]);

        \Log::info('OrderController@store request received', [
            'user_id' => auth()->id(),
            'items_count' => count($clientItems),
            'payment_method' => $request->payment_method,
            'branch_id' => $branchId
        ]);

        // 1) Canonical server calculation
        $calc = $this->cartCalculationService->calculate([
            'branch_id' => $branchId,
            'items' => $clientItems,
        ]);

        if (!empty($calc['unavailable'])) {
                return response()->json([
                'message' => 'Unavailable items',
                'unavailable' => $calc['unavailable'],
            ], 409);
        }

        $resolvedItems = $calc['items'];

        return DB::transaction(function() use ($request, $branchId, $resolvedItems, $calc) {
            // 2) Lock and re-validate per item
            foreach ($resolvedItems as $line) {
                $pid = (int) $line['product_id'];
                
                // Skip bulk packages for now (they don't have stock tracking)
                if ($line['type'] === 'bulk') {
                    continue;
                }
                
                // Lock branch stock/pivot row
                $pivot = DB::table('branch_product')
                    ->where('branch_id', $branchId)
                    ->where('product_id', $pid)
                    ->lockForUpdate()
                    ->first();

                if (!$pivot) {
                    throw new \DomainException("product_{$pid}:not_in_branch");
                }

                // If tracking stock:
                $qty = (int) $line['quantity'];
                $available = isset($pivot->stock) ? (int)$pivot->stock : PHP_INT_MAX;
                if ($available < $qty) {
                    throw new \DomainException("product_{$pid}:out_of_stock");
                }

                // Sanity: product active & not soft-deleted
                $prod = \App\Models\Product::withTrashed()->find($pid);
                if (!$prod || $prod->deleted_at || !$prod->is_active) {
                    throw new \DomainException("product_{$pid}:inactive");
                }
            }

            // 3) Create order from canonical calc (not from client)
            $order = \App\Models\Order::create([
                'user_id' => optional($request->user())->id,
                'branch_id' => $branchId,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'city' => $request->input('city'),
                'ward_number' => $request->input('ward_number'),
                'area_locality' => $request->input('area_locality'),
                'building_name' => $request->input('building_name'),
                'detailed_directions' => $request->input('detailed_directions'),
                'payment_method' => $request->input('payment_method'),
                'subtotal' => $calc['subtotal'],
                'delivery_fee' => $calc['delivery_fee'],
                'tax_amount' => $calc['tax'],
                'discount' => $calc['discount'] ?? 0,
                'grand_total' => $calc['total'],
                'order_type' => $request->input('order_type', 'online'),
                'status' => 'pending',
                'payment_status' => 'pending'
            ]);
            
            // Create order items from resolved items
            foreach ($resolvedItems as $line) {
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => (int)$line['product_id'],
                    'variant_id' => $line['variant_id'] ?? null,
                    'quantity' => (int)$line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'type' => $line['type'] ?? 'product',
                    'option_ids' => isset($line['option_ids']) && !empty($line['option_ids'])
                                      ? json_encode($line['option_ids'])
                                      : null,
                ]);

                // If stock is tracked here, decrement safely after checks:
                if ($line['type'] === 'product') {
                    $pid = (int) $line['product_id'];
                    $qty = (int) $line['quantity'];
                    DB::table('branch_product')
                        ->where('branch_id', $branchId)
                        ->where('product_id', $pid)
                        ->decrement('stock', $qty);
                }
            }
            
            // Handle wallet payment
            $walletAmount = 0;
            $walletPaymentProcessed = false;
            if ($request->payment_method === 'wallet' && auth()->check()) {
                $wallet = auth()->user()->wallet;
                if ($wallet) {
                    $walletAmount = min($wallet->credits_balance, $calc['total']);
                    
                    if ($walletAmount > 0) {
                        $walletPaymentProcessed = true;
                    } else {
                        // Wallet balance is insufficient
                        throw new \DomainException("wallet:insufficient_balance");
                    }
                } else {
                    // No wallet found
                    throw new \DomainException("wallet:not_found");
                }
            }
            
            // Update order with wallet payment if applicable
            if ($walletPaymentProcessed) {
                $order->update([
                    'wallet_payment' => $walletAmount,
                    'payment_status' => $walletAmount >= $calc['total'] ? 'paid' : 'partial'
                ]);
                
                // Deduct from wallet
                $wallet->decrement('credits_balance', $walletAmount);
            }
            
            // Add GPS location if provided
            if ($request->has('gps_location')) {
                $order->update([
                    'gps_location' => json_encode($request->gps_location)
                ]);
            }
            
            // Process referral if exists and user is authenticated
            if (auth()->check()) {
                $user = auth()->user();
                $referral = \App\Models\Referral::where('referred_user_id', $user->id)
                    ->where('status', 'registered')
                    ->first();

                if ($referral) {
                    $referralService = new \App\Services\ReferralService();
                    $referralService->processOrder($user, $referral, $order);
                }
            }
            
            \Log::info('OrderController@store order created successfully', [
                'order_id' => $order->id,
                'order_code' => $order->code ?? 'ORD-' . strtoupper(uniqid()),
                'total' => $calc['total']
            ]);
            
            return response()->json([
                'message' => 'Order created successfully',
                'order_id' => $order->id,
                'order_code' => $order->code ?? 'ORD-' . strtoupper(uniqid())
            ], 201);
        });
    }

    /**
     * Debug endpoint to test order creation with specific items
     */
    public function debugOrderCreation(Request $request)
    {
        try {
            $items = $request->input('items', []);
            $branchId = (int) ($request->input('branch_id', 1));
            
            \Log::info('Debug order creation', [
                'items' => $items,
                'branch_id' => $branchId
            ]);
            
            // Test cart calculation
            $cartCalculation = $this->cartCalculationService->calculate([
                'items' => $items,
                'branch_id' => $branchId
            ]);
            
            if (!empty($cartCalculation['unavailable'])) {
                return response()->json([
                    'success' => false,
                    'unavailable' => $cartCalculation['unavailable']
                ], 409);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'All items are valid',
                'cart_calculation' => $cartCalculation
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Debug order creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug endpoint to check available products
     */
    public function debugProducts(Request $request)
    {
        try {
            $products = Product::select('id', 'name', 'price', 'is_active', 'deleted_at')
                ->withTrashed()
                ->get();
            
            return response()->json([
                'success' => true,
                'products' => $products,
                'count' => $products->count(),
                'active_count' => $products->where('is_active', true)->whereNull('deleted_at')->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Debug endpoint to test order creation
     */
    public function debugOrder(Request $request)
    {
        try {
            $cartItems = $request->input('items', []);
            \Log::info('Debug order request', ['items' => $cartItems]);
            
            $processedItems = [];
            foreach ($cartItems as $item) {
                $productId = $item['product_id'];
                $type = $item['type'] ?? 'product';
                
                if ($type === 'bulk' && str_starts_with($productId, 'bulk-')) {
                    $bulkPackageId = str_replace('bulk-', '', $productId);
                    $bulkPackage = \App\Models\BulkPackage::find($bulkPackageId);
                    
                    if (!$bulkPackage) {
                        return response()->json([
                            'error' => "Bulk package with ID {$bulkPackageId} not found"
                        ], 400);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'processed_items' => $processedItems
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,unpaid',
            'payment_method' => 'required|in:cash,card',
            'amount_received' => 'required_if:payment_method,cash|numeric|min:0',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->payment_status === 'paid' ? 'completed' : $order->status;
        
        $order->update([
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method,
            'amount_received' => $request->amount_received,
            'change' => $request->payment_method === 'cash' ? $request->amount_received - $order->grand_total : 0,
            'status' => $newStatus,
        ]);

        // Send push notification if status changed and order has a user
        if ($oldStatus !== $newStatus && $newStatus === 'completed' && $order->user_id) {
            try {
                // Collect all device tokens for the order's user
                $tokens = \App\Models\Device::where('user_id', $order->user_id)->pluck('token')->all();
                
                if (!empty($tokens)) {
                    $orderCode = $order->code ?? 'ORD-' . strtoupper(uniqid());
                    
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->sendPushNotification(
                        $tokens,
                        "Order {$orderCode}",
                        "Status: completed",
                        [
                            'orderId' => $order->id, 
                            'code' => $orderCode, 
                            'status' => 'completed'
                        ]
                    );
                    
                    \Log::info('Push notification sent for order completion', [
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'tokens_count' => count($tokens)
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send push notification for order completion', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'message' => 'Payment status updated successfully',
            'order' => $order->load('items.product')
        ]);
    }

    public function paymentManager()
    {
        return redirect()->route('admin.dashboard')->with('error', 'Payment manager is currently unavailable.');
    }

    /**
     * Show order success page
     * 
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function success(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($order->payment->status !== 'completed') {
            return redirect()->route('orders.show', $order->id);
        }

        return view('orders.success', compact('order'));
    }
}
            if (auth()->check()) {
                PosAccessLog::create([
                    'user_id' => Auth::id(),
                    'access_type' => 'pos',
                    'action' => 'order',
                    'details' => [
                        'order_id' => $order->id,
                        'total_amount' => $order->total_amount,
                        'items_count' => count($request->items)
                    ],
                    'ip_address' => $request->ip()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $order->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            \Log::error('OrderController@store error creating order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->all()
            ]);
            DB::rollBack();
            
            // Provide more specific error messages and appropriate HTTP status codes
            $errorMessage = $e->getMessage();
            $statusCode = 500; // Default to server error
            
            if (str_contains($errorMessage, 'Integrity constraint violation')) {
                $errorMessage = 'Database constraint error. Please check your cart items.';
                $statusCode = 422; // Unprocessable Entity
            } elseif (str_contains($errorMessage, 'not found')) {
                $errorMessage = 'One or more items in your cart are no longer available.';
                $statusCode = 409; // Conflict - cart items are stale
            } elseif (str_contains($errorMessage, 'foreign key')) {
                $errorMessage = 'Invalid product reference. Please refresh your cart.';
                $statusCode = 422; // Unprocessable Entity
            } elseif (str_contains($errorMessage, 'no longer available') || str_contains($errorMessage, 'currently unavailable')) {
                $statusCode = 409; // Conflict - product availability issue
            }
            
            return response()->json([
                'message' => 'Error creating order',
                'error' => $errorMessage,
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], $statusCode);
        }
    }

    /**
     * Debug endpoint to test order creation with specific items
     */
    public function debugOrderCreation(Request $request)
    {
        try {
            $items = $request->input('items', []);
            $branchId = (int) ($request->input('branch_id', 1));
            
            \Log::info('Debug order creation', [
                'items' => $items,
                'branch_id' => $branchId
            ]);
            
            // Test cart calculation
            $cartCalculation = $this->cartCalculationService->calculateCartTotals($items, $branchId);
            
            if ($cartCalculation['has_errors']) {
                return response()->json([
                    'success' => false,
                    'errors' => $cartCalculation['errors']
                ], 409);
            }
            
            // Test product lookup for each item
            foreach ($cartCalculation['items'] as $item) {
                $productId = (int) $item['product_id'];
                $product = \App\Models\Product::withTrashed()->find($productId);
                
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'error' => "Product with ID {$productId} not found",
                        'item' => $item
                    ], 409);
                }
                
                if ($product->trashed()) {
                    return response()->json([
                        'success' => false,
                        'error' => "Product '{$product->name}' is no longer available",
                        'item' => $item
                    ], 409);
                }
                
                if (!$product->is_active) {
                    return response()->json([
                        'success' => false,
                        'error' => "Product '{$product->name}' is currently unavailable",
                        'item' => $item
                    ], 409);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'All items are valid',
                'cart_calculation' => $cartCalculation
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Debug order creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug endpoint to check available products
     */
    public function debugProducts(Request $request)
    {
        try {
            $products = Product::select('id', 'name', 'price', 'is_active', 'deleted_at')
                ->withTrashed()
                ->get();
            
            return response()->json([
                'success' => true,
                'products' => $products,
                'count' => $products->count(),
                'active_count' => $products->where('is_active', true)->whereNull('deleted_at')->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Debug endpoint to test order creation
     */
    public function debugOrder(Request $request)
    {
        try {
            $cartItems = $request->input('items', []);
            \Log::info('Debug order request', ['items' => $cartItems]);
            
            $processedItems = [];
            foreach ($cartItems as $item) {
                $productId = $item['product_id'];
                $type = $item['type'] ?? 'product';
                
                if ($type === 'bulk' && str_starts_with($productId, 'bulk-')) {
                    $bulkPackageId = str_replace('bulk-', '', $productId);
                    $bulkPackage = \App\Models\BulkPackage::find($bulkPackageId);
                    
                    if (!$bulkPackage) {
                        return response()->json([
                            'error' => "Bulk package with ID {$bulkPackageId} not found"
                        ], 400);
                    }
                    
                    $processedItems[] = [
                        'original_id' => $productId,
                        'bulk_package_id' => $bulkPackageId,
                        'name' => $bulkPackage->name,
                        'price' => $bulkPackage->bulk_price ?? $bulkPackage->total_price,
                        'type' => 'bulk'
                    ];
                } else {
                    $product = Product::find($productId);
                    if (!$product) {
                        return response()->json([
                            'error' => "Product with ID {$productId} not found"
                        ], 400);
                    }
                    
                    $processedItems[] = [
                        'original_id' => $productId,
                        'product_id' => $productId,
                        'name' => $product->name,
                        'price' => $product->price,
                        'type' => 'product'
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'processed_items' => $processedItems
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function updatePaymentStatus(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,unpaid',
            'payment_method' => 'required|in:cash,card',
            'amount_received' => 'required_if:payment_method,cash|numeric|min:0',
        ]);

        $oldStatus = $order->status;
        $newStatus = $request->payment_status === 'paid' ? 'completed' : $order->status;
        
        $order->update([
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method,
            'amount_received' => $request->amount_received,
            'change' => $request->payment_method === 'cash' ? $request->amount_received - $order->grand_total : 0,
            'status' => $newStatus,
        ]);

        // Send push notification if status changed and order has a user
        if ($oldStatus !== $newStatus && $newStatus === 'completed' && $order->user_id) {
            try {
                // Collect all device tokens for the order's user
                $tokens = \App\Models\Device::where('user_id', $order->user_id)->pluck('token')->all();
                
                if ($tokens) {
                    $orderCode = $order->code ?: '#' . $order->id;
                    app(\App\Services\ExpoPushService::class)->send(
                        $tokens,
                        "Order {$orderCode}",
                        "Status: completed",
                        [
                            'orderId' => $order->id, 
                            'code' => $orderCode, 
                            'status' => 'completed'
                        ]
                    );
                    
                    \Log::info('Push notification sent for order completion', [
                        'order_id' => $order->id,
                        'user_id' => $order->user_id,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'tokens_count' => count($tokens)
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send push notification for order completion', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'message' => 'Payment status updated successfully',
            'order' => $order->load('items.product')
        ]);
    }

    public function paymentManager()
    {
        return redirect()->route('admin.dashboard')->with('error', 'Payment manager is currently unavailable.');
    }

    protected function handleCreatorPoints($order)
    {
        if ($order->referral && $order->referral->creator) {
            $creator = $order->referral->creator;
            $this->creatorPointsService->awardPoints(
                $creator,
                5,
                'Points earned for completed order #' . $order->id
            );
        }
    }

    /**
     * Show the order success page
     *
     * @param Order $order
     * @return \Illuminate\View\View
     */
    public function success(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        if ($order->payment->status !== 'completed') {
            return redirect()->route('orders.show', $order->id);
        }

        return view('orders.success', compact('order'));
    }
} 