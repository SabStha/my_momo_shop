<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(
                Order::with('items.product')
                    ->latest()
                    ->paginate(10)
            );
        }

        $query = Order::query();
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        $orders = $query->with(['table', 'items', 'user'])->latest()->paginate(20);
        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items', 'table', 'user']);
        return view('orders.show', compact('order'));
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

            DB::commit();

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
        $orders = \App\Models\Order::latest()->get();
        return view('payment-manager', compact('orders'));
    }
} 