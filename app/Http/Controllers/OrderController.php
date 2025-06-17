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

class OrderController extends Controller
{
    protected $creatorPointsService;

    public function __construct(CreatorPointsService $creatorPointsService)
    {
        $this->creatorPointsService = $creatorPointsService;
        $this->middleware('auth');
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
            : 'dashboard.orders.show';

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
            return response()->json(['success' => true, 'message' => 'Payment processed!']);
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
        $request->validate([
            'type' => 'required|in:dine_in,takeaway,delivery',
            'table_id' => 'required_if:type,dine_in|exists:tables,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card',
            'amount_received' => 'required_if:payment_method,cash|numeric|min:0',
            'guest_name' => 'required_if:type,delivery|string',
            'guest_email' => 'required_if:type,delivery|email',
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $totalAmount = 0;
            $items = collect($request->items)->map(function ($item) use (&$totalAmount) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;
                return [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ];
            });

            $taxAmount = $totalAmount * 0.13; // 13% tax
            $grandTotal = $totalAmount + $taxAmount;

            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'type' => $request->type,
                'table_id' => $request->table_id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method,
                'amount_received' => $request->amount_received,
                'change' => $request->payment_method === 'cash' ? $request->amount_received - $grandTotal : 0,
                'guest_name' => $request->guest_name,
                'guest_email' => $request->guest_email,
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'created_by' => $request->input('created_by'),
            ]);

            // Create order items
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal']
                ]);
            }

            // Update table status if dine-in
            if ($request->type === 'dine_in') {
                Table::where('id', $request->table_id)->update(['status' => 'occupied']);
            }

            // Process referral if exists
            $user = auth()->user();
            $referral = Referral::where('referred_id', $user->id)
                ->where('status', 'registered')
                ->first();

                if ($referral) {
                $referralService = new ReferralService();
                $referralService->processOrder($user, $referral, $order);
                        }

            DB::commit();

            // Log the order creation
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

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating order',
                'error' => $e->getMessage()
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

        $order->update([
            'payment_status' => $request->payment_status,
            'payment_method' => $request->payment_method,
            'amount_received' => $request->amount_received,
            'change' => $request->payment_method === 'cash' ? $request->amount_received - $order->grand_total : 0,
            'status' => $request->payment_status === 'paid' ? 'completed' : $order->status,
        ]);

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