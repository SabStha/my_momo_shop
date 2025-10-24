<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartCalculationService;
use App\Services\CreatorPointsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Get inline orders (pos/dine-in) and regular online orders separately
        $inlineOrders = Order::whereIn('order_type', ['pos', 'dine_in'])
            ->with(['items.product', 'table', 'user'])
            ->latest()
            ->take(50)
            ->get();

        $posOrders = Order::where('order_type', 'pos')
            ->with(['items.product', 'user'])
            ->latest()
            ->take(50)
            ->get();

        $orderHistory = Order::where('order_type', 'online')
            ->with(['items.product', 'user'])
            ->latest()
            ->take(100)
            ->get();

        return view('orders.index', compact('inlineOrders', 'posOrders', 'orderHistory'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'user', 'table']);
        
        // Determine which view to use based on order type
        $view = match($order->order_type) {
            'pos' => 'orders.show-pos',
            'dine_in' => 'orders.show-dine-in',
            default => 'orders.show'
        };

        return view($view, compact('order'));
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
            'payment_method' => 'required|in:cash,card,wallet,fonepay,esewa,khalti,amako_credits',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.type' => 'nullable|string|in:product,bulk',
            'total' => 'required|numeric|min:0',
            'applied_offer' => 'nullable|string',
            'gps_location' => 'nullable|array',
        ]);

        Log::info('OrderController@store request received', [
            'user_id' => auth()->id(),
            'items_count' => count($clientItems),
            'payment_method' => $request->payment_method,
            'branch_id' => $branchId,
            'request_all' => $request->all()
        ]);

        // Check if business is open (cash drawer status)
        $cashDrawerSession = \App\Models\CashDrawerSession::where('branch_id', $branchId)
            ->whereNull('closed_at')
            ->first();

        Log::info('Cash drawer session check', [
            'branch_id' => $branchId,
            'session_found' => $cashDrawerSession ? 'yes' : 'no',
            'session_id' => $cashDrawerSession?->id ?? 'none'
        ]);

        if (!$cashDrawerSession) {
            Log::warning('Order rejected - business closed', [
                'branch_id' => $branchId,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'We are currently closed. Please try again during business hours.',
                'business_status' => 'closed'
            ], 423); // 423 Locked - business is closed
        }
        
        Log::info('Session check passed - proceeding with order', [
            'session_id' => $cashDrawerSession->id,
            'branch_id' => $branchId
        ]);

        try {
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
                // 2) Re-validate per item (simplified for current database structure)
                foreach ($resolvedItems as $line) {
                    $pid = (int) $line['product_id'];
                    
                    // Skip bulk packages for now (they don't have stock tracking)
                    if ($line['type'] === 'bulk') {
                        continue;
                    }
                    
                    // Final sanity check: product active & not soft-deleted
                    $prod = Product::withTrashed()->find($pid);
                    if (!$prod || $prod->deleted_at || !$prod->is_active) {
                        throw new \DomainException("product_{$pid}:inactive");
                    }
                    
                    // TODO: Add stock tracking when branch-product relationships are implemented
                    // For now, assume all products have unlimited stock
                }

                // 3) Create order from canonical calc (not from client)
                $order = Order::create([
                    'user_id' => optional($request->user())->id,
                    'branch_id' => $branchId,
                    'customer_name' => $request->input('name'),
                    'customer_email' => $request->input('email'),
                    'customer_phone' => $request->input('phone'),
                    'delivery_address' => json_encode([
                        'city' => $request->input('city'),
                        'ward_number' => $request->input('ward_number'),
                        'area_locality' => $request->input('area_locality'),
                        'building_name' => $request->input('building_name'),
                        'detailed_directions' => $request->input('detailed_directions'),
                    ]),
                    'payment_method' => $request->input('payment_method'),
                    'subtotal' => $calc['subtotal'],
                    'tax_amount' => $calc['tax'],
                    'discount' => $calc['discount'] ?? 0,
                    'total' => $calc['total'],
                    'total_amount' => $calc['total'],
                    'grand_total' => $calc['total'],
                    'order_type' => $request->input('order_type', 'online'),
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'order_number' => 'ORD-' . strtoupper(uniqid())
                ]);
                
                // Create order items from resolved items
                foreach ($resolvedItems as $line) {
                    // Get product name for the item
                    $product = Product::find($line['product_id']);
                    $itemName = $product ? $product->name : 'Unknown Product';
                    
                    \App\Models\OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => (int)$line['product_id'],
                        'item_name' => $itemName,
                        'quantity' => (int)$line['quantity'],
                        'price' => $line['unit_price'],
                        'subtotal' => $line['unit_price'] * (int)$line['quantity'],
                    ]);

                    // TODO: Add stock decrementing when branch-product relationships are implemented
                    // For now, skip stock tracking
                }
                
                // Handle wallet/amako_credits payment
                $walletAmount = 0;
                $walletPaymentProcessed = false;
                $paymentMethod = $request->payment_method;
                
                // Support both 'wallet' and 'amako_credits' payment methods
                if (in_array($paymentMethod, ['wallet', 'amako_credits']) && auth()->check()) {
                    $wallet = auth()->user()->wallet;
                    if ($wallet) {
                        $walletAmount = min($wallet->credits_balance, $calc['total']);
                        
                        \Log::info('Wallet payment processing', [
                            'user_id' => auth()->id(),
                            'wallet_balance' => $wallet->credits_balance,
                            'order_total' => $calc['total'],
                            'amount_to_deduct' => $walletAmount
                        ]);
                        
                        if ($walletAmount > 0) {
                            $walletPaymentProcessed = true;
                        } else {
                            // Wallet balance is insufficient
                            throw new \DomainException("wallet:insufficient_balance");
                        }
                    } else {
                        // No wallet found
                        \Log::warning('Wallet not found for user', ['user_id' => auth()->id()]);
                        throw new \DomainException("wallet:not_found");
                    }
                }
                
                // Update order with wallet payment if applicable
                if ($walletPaymentProcessed) {
                    $order->update([
                        'wallet_payment' => $walletAmount,
                        'payment_status' => $walletAmount >= $calc['total'] ? 'paid' : 'partial'
                    ]);
                    
                    // Deduct from wallet using the proper method
                    $wallet->addBalance($walletAmount, 'debit');
                    
                    // Create wallet transaction record
                    \App\Models\WalletTransaction::create([
                        'credits_account_id' => $wallet->id,
                        'user_id' => auth()->id(),
                        'type' => 'debit',
                        'credits_amount' => $walletAmount,
                        'description' => "Payment for Order #{$order->order_number}",
                        'status' => 'completed',
                        'credits_balance_before' => $wallet->credits_balance + $walletAmount,
                        'credits_balance_after' => $wallet->credits_balance,
                    ]);
                    
                    \Log::info('Wallet payment completed', [
                        'order_id' => $order->id,
                        'amount_deducted' => $walletAmount,
                        'new_balance' => $wallet->credits_balance
                    ]);
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
                
                // Mark applied offer as used
                if ($request->has('applied_offer') && auth()->check()) {
                    $offerCode = $request->input('applied_offer');
                    
                    Log::info('Processing applied offer', [
                        'offer_code' => $offerCode,
                        'order_id' => $order->id,
                        'user_id' => auth()->id()
                    ]);
                    
                    try {
                        $offerClaim = \App\Models\OfferClaim::with('offer')
                            ->where('user_id', auth()->id())
                            ->whereHas('offer', function($q) use ($offerCode) {
                                $q->where('code', $offerCode);
                            })
                            ->where('status', 'active')
                            ->first();
                        
                        if ($offerClaim) {
                            // Mark as used
                            $offerClaim->update([
                                'status' => 'used',
                                'used_at' => now(),
                                'order_id' => $order->id,
                                'discount_applied' => $calc['discount'] ?? 0,
                            ]);
                            
                            // Track analytics
                            \App\Models\OfferAnalytics::create([
                                'offer_id' => $offerClaim->offer_id,
                                'user_id' => auth()->id(),
                                'action' => 'used',
                                'timestamp' => now(),
                                'discount_value' => $calc['discount'] ?? 0,
                                'device_info' => json_encode(['user_agent' => $request->header('User-Agent')]),
                                'session_data' => json_encode(['order_id' => $order->id]),
                            ]);
                            
                            Log::info('Offer marked as used', [
                                'offer_claim_id' => $offerClaim->id,
                                'offer_code' => $offerCode,
                                'order_id' => $order->id,
                                'discount' => $calc['discount'] ?? 0
                            ]);
                        } else {
                            Log::warning('Offer claim not found', [
                                'offer_code' => $offerCode,
                                'user_id' => auth()->id()
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Don't fail the order if offer tracking fails
                        Log::error('Failed to mark offer as used', [
                            'error' => $e->getMessage(),
                            'offer_code' => $offerCode,
                            'order_id' => $order->id
                        ]);
                    }
                }
                
                Log::info('OrderController@store order created successfully', [
                    'order_id' => $order->id,
                    'order_code' => $order->code ?? 'ORD-' . strtoupper(uniqid()),
                    'total' => $calc['total']
                ]);
                
                return response()->json([
                    'message' => 'Order created successfully',
                    'order_id' => $order->id,
                    'order_code' => $order->code ?? 'ORD-' . strtoupper(uniqid()),
                    'order_number' => $order->order_number,
                    'subtotal' => $order->subtotal,
                    'tax_amount' => $order->tax_amount,
                    'delivery_fee' => $calc['delivery_fee'] ?? 0,
                    'total_amount' => $order->total_amount,
                    'grand_total' => $order->grand_total,
                    'status' => $order->status
                ], 201);
            });

        } catch (\DomainException $e) {
            $msg = $e->getMessage(); // e.g. "product_4:out_of_stock"
            [$tag, $reason] = explode(':', $msg) + [null, 'unknown'];
            
            if (str_starts_with($tag, 'product_')) {
                $pid = (int) str_replace('product_', '', $tag);
                return response()->json([
                    'message' => 'Unavailable items',
                    'unavailable' => [['product_id' => $pid, 'reason' => $reason]],
                ], 409);
            } elseif (str_starts_with($tag, 'wallet:')) {
                return response()->json([
                    'message' => 'Wallet error',
                    'error' => $reason === 'insufficient_balance' ? 'Insufficient wallet balance' : 'Wallet not found'
                ], 400);
            }
            
            return response()->json([
                'message' => 'Order creation failed',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            Log::error('OrderController@store error creating order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'message' => 'Error creating order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function debugOrderCreation(Request $request)
    {
        try {
            $items = $request->input('items', []);
            $branchId = (int) ($request->input('branch_id', 1));
            
            Log::info('Debug order creation', [
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
            Log::error('Debug order creation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function success(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('orders.success', compact('order'));
    }
}
