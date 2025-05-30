<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Http\Request;

class PosOrderController extends Controller
{
    // List all open orders (pending, preparing, prepared)
    public function index()
    {
        $orders = Order::with(['table', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($orders);
    }

    // Create a new order (with items)
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'type' => 'required|in:dine-in,takeaway,online',
                'table_id' => 'nullable|exists:tables,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            $order = Order::create([
                'type' => $data['type'],
                'table_id' => $data['table_id'] ?? null,
                'status' => 'pending',
                'total' => 0,
                'total_amount' => 0.00,
                'payment_method' => 'cash', // default for dine-in
                'shipping_address' => 'N/A',
                'billing_address' => 'N/A',
                'created_by' => $request->input('created_by'),
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'item_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }
            $taxAmount = $total * 0.13;
            $grandTotal = $total + $taxAmount;
            $order->update([
                'total' => $total,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'total_amount' => $total,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'payment_status' => 'unpaid'
            ]);

            // Optionally update table status
            if ($order->type === 'dine-in' && $order->table_id) {
                $order->table->update(['status' => 'occupied']);
            }

            return response()->json(['order' => $order->load(['table', 'items'])], 201);
        } catch (\Throwable $e) {
            \Log::error('Order submission error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    // Show order details
    public function show(Order $order)
    {
        $order->load(['table', 'items', 'payments']);
        return response()->json($order);
    }

    // Update order status
    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,preparing,prepared,completed',
        ]);
        $order->update(['status' => $data['status']]);

        // Optionally free up table if completed
        if ($order->status === 'completed' && $order->table_id) {
            $order->table->update(['status' => 'available']);
        }

        return response()->json(['message' => 'Order status updated', 'order' => $order]);
    }

    // Delete an order
    public function destroy(Order $order)
    {
        try {
            $order->delete();
            return response()->json(['message' => 'Order deleted successfully']);
        } catch (\Exception $e) {
            \Log::error('Order deletion error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete order'], 500);
        }
    }

    // Update an order (type, table, items)
    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'type' => 'required|in:dine-in,takeaway,online',
            'table_id' => 'nullable|exists:tables,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        // Update order type and table
        $order->type = $data['type'];
        $order->table_id = $data['type'] === 'dine-in' ? $data['table_id'] : null;
        $order->save();

        // Remove all existing items and re-add (simple approach)
        $order->items()->delete();
        $total = 0;
        foreach ($data['items'] as $item) {
            if ($item['quantity'] > 0) {
                $product = \App\Models\Product::find($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                $total += $subtotal;
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'item_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
                ]);
            }
        }
        $order->update(['total' => $total]);

        // Optionally update table status
        if ($order->type === 'dine-in' && $order->table_id) {
            $order->table->update(['status' => 'occupied']);
        }

        return response()->json($order->load(['table', 'items']));
    }
} 