<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $branchId = $request->query('branch');
            
            if (!$branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Branch ID is required'
                ], 400);
            }

            $orders = Order::with(['items.product', 'table'])
                ->where('branch_id', $branchId)
                ->where('status', '!=', 'completed')
                ->orderBy('created_at', 'desc')
                ->get();

            \Log::info('Loading orders for branch', [
                'branch_id' => $branchId,
                'count' => $orders->count(),
                'order_ids' => $orders->pluck('id')->toArray()
            ]);

            return response()->json([
                'success' => true,
                'orders' => $orders
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load orders'
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $order = Order::with(['items', 'table', 'user'])->findOrFail($id);
            return response()->json($order);
        } catch (\Exception $e) {
            Log::error('Error fetching order: ' . $e->getMessage());
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());

            // Calculate total
            $total = 0;
            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $total += $product->price * $item['quantity'];
            }

            $order = Order::create([
                'order_number' => $orderNumber,
                'type' => $request->type,
                'table_id' => $request->table_id,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'total_amount' => $total,
                'created_by' => $request->user()->id
            ]);

            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'item_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $product->price * $item['quantity']
                ]);
            }

            DB::commit();

            // Return the formatted order data
            $order->load(['items.product', 'table', 'user']);
            $formattedOrder = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'type' => $order->type,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'total_amount' => (float) $order->total_amount,
                'table' => $order->table ? [
                    'id' => $order->table->id,
                    'name' => $order->table->name,
                    'status' => $order->table->status
                ] : null,
                'items' => $order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'item_name' => $item->product ? $item->product->name : $item->item_name,
                        'quantity' => (int) $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal
                    ];
                }),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at
            ];

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'order' => $formattedOrder
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            
            $order->update([
                'status' => $request->status,
                'payment_status' => $request->payment_status,
                'total' => $request->total,
                'notes' => $request->notes
            ]);

            return response()->json([
                'message' => 'Order updated successfully',
                'order' => $order->load(['items', 'table', 'user'])
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating order'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();
            return response()->json(['message' => 'Order deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting order'], 500);
        }
    }

    public function processPayment(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,unpaid',
            'payment_method' => 'required|in:cash,card,qr',
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
            'success' => true,
            'message' => 'Payment processed successfully',
            'order' => $order->load('items.product', 'table')
        ]);
    }
} 