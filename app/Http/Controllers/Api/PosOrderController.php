<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosOrderController extends Controller
{
    // List all open orders (pending, preparing, prepared)
    public function index()
    {
        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        $orders = Order::with(['items', 'table'])
            ->where('branch_id', $branchId)
            ->where('status', '!=', 'completed')
            ->get();

        return response()->json($orders);
    }

    // Create a new order (with items)
    public function store(Request $request)
    {
        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        $request->validate([
            'table_id' => 'required|exists:tables,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'branch_id' => $branchId,
                'table_id' => $request->table_id,
                'status' => 'pending',
                'total' => 0,
            ]);

            $total = 0;
            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('branch_id', $branchId)
                    ->firstOrFail();

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);

                $total += $orderItem->subtotal;
            }

            $order->update(['total' => $total]);
            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('items')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create order: ' . $e->getMessage()], 500);
        }
    }

    // Show order details
    public function show(Order $order)
    {
        $this->authorize('view', $order);
        
        $order->load(['table', 'items', 'payments']);
        return response()->json([
            'success' => true,
            'order' => new OrderResource($order)
        ]);
    }

    // Update order status
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $data = $request->validated();
        
        try {
            \DB::transaction(function () use ($order, $data) {
                $order->status = $data['status'];
                $order->save();

                // Free up table if completed
                if ($order->status === 'completed' && $order->table_id) {
                    \App\Models\Table::where('id', $order->table_id)
                        ->update(['status' => 'available']);
                }

                \Log::info('Order status updated', [
                    'order_id' => $order->id,
                    'old_status' => $order->getOriginal('status'),
                    'new_status' => $order->status,
                    'updated_by' => auth()->id(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'order' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update order status', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status'
            ], 500);
        }
    }

    // Delete an order
    public function destroy(Order $order)
    {
        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        if ($order->branch_id !== $branchId) {
            return response()->json(['error' => 'Order does not belong to current branch'], 403);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    // Update an order (type, table, items)
    public function update(Request $request, Order $order)
    {
        $branchId = session('selected_branch_id');
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        if ($order->branch_id !== $branchId) {
            return response()->json(['error' => 'Order does not belong to current branch'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load('items')
        ]);
    }
} 