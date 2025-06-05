<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use Illuminate\Http\Request;

class PosOrderController extends Controller
{
    // List all open orders (pending, preparing, prepared)
    public function index()
    {
        $this->authorize('viewAny', Order::class);
        
        $query = Order::with(['table', 'items.product', 'payments', 'createdBy:id,name']);
        
        // Employees can only see orders they created
        if (auth()->user()->hasRole('employee') && !auth()->user()->hasAnyRole(['admin', 'cashier'])) {
            $query->where('created_by', auth()->id());
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'orders' => OrderResource::collection($orders)
        ]);
    }

    // Create a new order (with items)
    public function store(CreateOrderRequest $request)
    {
        $data = $request->validated();

        try {
            return \DB::transaction(function () use ($data, $request) {
                // Generate order number first
                $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
                
                // Create order with only allowed fields
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'type' => $data['type'],
                    'table_id' => $data['table_id'] ?? null,
                    'status' => 'pending',
                    'payment_method' => 'cash',
                    'payment_status' => 'unpaid',
                    'guest_name' => $data['guest_name'] ?? null,
                    'guest_email' => $data['guest_email'] ?? null,
                    'shipping_address' => 'N/A',
                    'billing_address' => 'N/A',
                    'created_by' => auth()->id(),
                ]);

                $total = 0;
                $orderItems = [];

                // Process items and calculate totals
                foreach ($data['items'] as $item) {
                    $product = \App\Models\Product::findOrFail($item['product_id']);
                    
                    // Verify product is active
                    if (!$product->active) {
                        throw new \Exception("Product {$product->name} is not available");
                    }
                    
                    $quantity = (int) $item['quantity'];
                    $price = (float) $product->price;
                    $subtotal = $price * $quantity;
                    $total += $subtotal;

                    $orderItems[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'item_name' => $product->name,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Bulk insert order items for better performance
                OrderItem::insert($orderItems);

                // Calculate tax and grand total
                $taxRate = config('momo.tax_rate', 0.13);
                $taxAmount = round($total * $taxRate, 2);
                $grandTotal = round($total + $taxAmount, 2);

                // Update order with calculated amounts using model methods (not mass assignment)
                $order->total_amount = $total;
                $order->tax_amount = $taxAmount;
                $order->grand_total = $grandTotal;
                $order->save();

                // Update table status if dine-in
                if ($order->type === 'dine-in' && $order->table_id) {
                    \App\Models\Table::where('id', $order->table_id)
                        ->update(['status' => 'occupied']);
                }

                // Log order creation
                \Log::info('Order created successfully', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total_amount' => $order->total_amount,
                    'created_by' => auth()->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'order' => new OrderResource($order->load(['table', 'items'])),
                    'message' => 'Order created successfully'
                ], 201);
            });
        } catch (\Throwable $e) {
            \Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request_data' => $data,
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], 500);
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
        $this->authorize('delete', $order);
        
        try {
            \DB::transaction(function () use ($order) {
                // Free up table if order had one
                if ($order->table_id) {
                    \App\Models\Table::where('id', $order->table_id)
                        ->update(['status' => 'available']);
                }
                
                // Delete related items first
                $order->items()->delete();
                $order->payments()->delete();
                
                // Delete the order
                $order->delete();
                
                \Log::info('Order deleted successfully', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'deleted_by' => auth()->id(),
                ]);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Order deletion failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order'
            ], 500);
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