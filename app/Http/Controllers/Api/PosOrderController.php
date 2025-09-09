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
use App\Services\ActivityLogService;

class PosOrderController extends Controller
{
    // List all open orders (pending, preparing, prepared)
    public function index(Request $request)
    {
        // Get branch ID from session or request header
        $branchId = session('selected_branch_id') ?? $request->header('X-Branch-ID') ?? $request->query('branch_id');
        
        \Log::info('Loading orders for branch', [
            'branch_id' => $branchId,
            'session_branch' => session('selected_branch_id'),
            'header_branch' => $request->header('X-Branch-ID'),
            'query_branch' => $request->query('branch_id')
        ]);
        
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        // Strictly filter orders by branch ID
        $orders = Order::with(['items', 'table'])
            ->where('branch_id', $branchId)
            ->where('status', '!=', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::info('Found orders', [
            'branch_id' => $branchId,
            'count' => $orders->count(),
            'order_ids' => $orders->pluck('id')->toArray()
        ]);

        return response()->json($orders);
    }

    // Create a new order (with items)
    public function store(Request $request)
    {
        try {
            $branchId = $request->header('X-Branch-ID');
            if (!$branchId) {
                return response()->json(['error' => 'Branch ID is required'], 400);
            }

            // Validate request
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'order_type' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if (!in_array($value, ['dine_in', 'takeaway'])) {
                            $fail('Invalid order type. Must be either dine_in or takeaway.');
                        }
                    }
                ],
                'table_id' => [
                    'nullable',
                    'integer',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->order_type === 'dine_in' && !$value) {
                            $fail('Table ID is required for dine-in orders.');
                        }
                        if ($value && !Table::where('id', $value)->exists()) {
                            $fail('The selected table does not exist.');
                        }
                    }
                ],
                'subtotal' => 'required|numeric|min:0',
                'tax' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0'
            ]);

            \Log::info('Creating order with request data:', [
                'branch_id' => $branchId,
                'order_type' => $request->order_type,
                'table_id' => $request->table_id,
                'items_count' => count($request->items)
            ]);

            DB::beginTransaction();

            // Validate all products exist
            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])->first();

                if (!$product) {
                    throw new \Exception("Product ID {$item['product_id']} not found");
                }
            }

            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());

            // Log the order type and table ID before creation
            \Log::info('Creating order with details', [
                'order_type' => $request->order_type,
                'table_id' => $request->table_id,
                'is_dine_in' => $request->order_type === 'dine_in'
            ]);

            // Create the order
            $order = Order::create([
                'branch_id' => $branchId,
                'user_id' => auth()->id(),
                'order_number' => $orderNumber,
                'order_type' => $request->order_type,
                'table_id' => $request->order_type === 'dine_in' ? $request->table_id : null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'subtotal' => $request->subtotal,
                'tax' => $request->tax,
                'total' => $request->total
            ]);

            \Log::info('Order created successfully', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type
            ]);

            // Create order items
            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])->first();

                $subtotal = $item['quantity'] * $item['price'];
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'item_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal
                ]);
            }

            // Update table status if it's a dine-in order
            if ($request->order_type === 'dine_in' && $request->table_id) {
                $table = Table::find($request->table_id);
                if ($table) {
                    $updated = $table->updateStatus('occupied', true);
                    if (!$updated) {
                        throw new \Exception('Failed to update table status');
                    }
                }
            }

            // Log the order creation activity
            ActivityLogService::logPosActivity(
                'create',
                'New order created',
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                    'table_id' => $order->table_id,
                    'total' => $order->total,
                    'items_count' => count($request->items),
                    'user_id' => auth()->id(),
                    'branch_id' => $branchId
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load('items.product')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating order: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Show order details
    public function show(Order $order)
    {
        // Get branch ID from session or request header
        $branchId = session('selected_branch_id') ?? request()->header('X-Branch-ID');
        
        \Log::info('Showing order details', [
            'order_id' => $order->id,
            'order_branch' => $order->branch_id,
            'current_branch' => $branchId,
            'session_branch' => session('selected_branch_id'),
            'header_branch' => request()->header('X-Branch-ID')
        ]);

        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        // Convert branch ID to integer for comparison
        $branchId = (int)$branchId;
        $orderBranchId = (int)$order->branch_id;

        if ($orderBranchId !== $branchId) {
            \Log::warning('Branch mismatch when showing order', [
                'order_id' => $order->id,
                'order_branch' => $orderBranchId,
                'current_branch' => $branchId
            ]);
            return response()->json(['error' => 'Order does not belong to current branch'], 403);
        }
        
        $order->load(['table', 'items', 'payments']);
        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }

    // Update order status
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        $data = $request->validated();
        
        try {
            \DB::transaction(function () use ($order, $data) {
                $oldStatus = $order->status;
                $order->status = $data['status'];
                $order->save();

                // Log the status change
                ActivityLogService::logPosActivity(
                    'update',
                    'Order status updated',
                    [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'old_status' => $oldStatus,
                        'new_status' => $order->status,
                        'user_id' => auth()->id(),
                        'branch_id' => $order->branch_id
                    ]
                );

                // Free up table if completed
                if ($order->status === 'completed' && $order->table_id) {
                    \Log::info('Attempting to update table status after order completion', [
                        'order_id' => $order->id,
                        'table_id' => $order->table_id,
                        'branch_id' => $order->branch_id,
                        'order_type' => $order->order_type,
                        'order_status' => $order->status,
                        'payment_status' => $order->payment_status
                    ]);

                    $table = \App\Models\Table::where('id', $order->table_id)
                        ->where('branch_id', $order->branch_id)
                        ->first();

                    if ($table) {
                        $updated = $table->updateStatus('available', false);
                        if (!$updated) {
                            \Log::error('Failed to update table status after order completion', [
                                'table_id' => $table->id,
                                'branch_id' => $table->branch_id,
                                'order_id' => $order->id,
                                'timestamp' => now()
                            ]);
                            throw new \Exception('Failed to update table status');
                        }
                    } else {
                        \Log::warning('Table not found or branch mismatch during order completion', [
                            'table_id' => $order->table_id,
                            'branch_id' => $order->branch_id,
                            'order_id' => $order->id,
                            'order_type' => $order->order_type,
                            'order_status' => $order->status,
                            'payment_status' => $order->payment_status,
                            'timestamp' => now()->toDateTimeString()
                        ]);
                    }
                }

                \Log::info('Order status updated', [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $order->status,
                    'updated_by' => auth()->id(),
                ]);

                // Send push notification if status changed and order has a user
                if ($oldStatus !== $order->status && $order->user_id) {
                    try {
                        // Collect all device tokens for the order's user
                        $tokens = \App\Models\Device::where('user_id', $order->user_id)->pluck('token')->all();
                        
                        if ($tokens) {
                            $orderCode = $order->code ?: '#' . $order->id;
                            app(\App\Services\ExpoPushService::class)->send(
                                $tokens,
                                "Order {$orderCode}",
                                "Status: {$order->status}",
                                [
                                    'orderId' => $order->id, 
                                    'code' => $orderCode, 
                                    'status' => $order->status
                                ]
                            );
                            
                            \Log::info('Push notification sent for order status update', [
                                'order_id' => $order->id,
                                'user_id' => $order->user_id,
                                'old_status' => $oldStatus,
                                'new_status' => $order->status,
                                'tokens_count' => count($tokens)
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to send push notification for order status update', [
                            'order_id' => $order->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
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
        // Get branch ID from session or request header
        $branchId = session('selected_branch_id') ?? request()->header('X-Branch-ID');
        
        \Log::info('Deleting order', [
            'order_id' => $order->id,
            'order_branch' => $order->branch_id,
            'current_branch' => $branchId,
            'session_branch' => session('selected_branch_id'),
            'header_branch' => request()->header('X-Branch-ID')
        ]);
        
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        // Convert branch IDs to integers for comparison
        $branchId = (int)$branchId;
        $orderBranchId = (int)$order->branch_id;

        if ($orderBranchId !== $branchId) {
            \Log::warning('Branch mismatch when deleting order', [
                'order_id' => $order->id,
                'order_branch' => $orderBranchId,
                'current_branch' => $branchId
            ]);
            return response()->json(['error' => 'Order does not belong to current branch'], 403);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    // Update an order (type, table, items)
    public function update(Request $request, Order $order)
    {
        // Get branch ID from session or request header
        $branchId = session('selected_branch_id') ?? $request->header('X-Branch-ID');
        
        \Log::info('Updating order', [
            'order_id' => $order->id,
            'order_branch' => $order->branch_id,
            'current_branch' => $branchId,
            'session_branch' => session('selected_branch_id'),
            'header_branch' => $request->header('X-Branch-ID')
        ]);
        
        if (!$branchId) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        // Convert branch IDs to integers for comparison
        $branchId = (int)$branchId;
        $orderBranchId = (int)$order->branch_id;

        if ($orderBranchId !== $branchId) {
            \Log::warning('Branch mismatch when updating order', [
                'order_id' => $order->id,
                'order_branch' => $orderBranchId,
                'current_branch' => $branchId
            ]);
            return response()->json(['error' => 'Order does not belong to current branch'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:0',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Update order status
            $order->update(['status' => $request->status]);

            // Update order items
            $order->items()->delete(); // Remove existing items
            $total = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $item['quantity'] * $item['price'];
                
                $order->items()->create([
                    'product_id' => $product->id,
                    'item_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $subtotal
                ]);

                $total += $subtotal;
            }

            // Update order total
            $order->update(['total' => $total]);

            DB::commit();

            \Log::info('Order updated successfully', [
                'order_id' => $order->id,
                'branch_id' => $branchId,
                'total' => $total
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'order' => $order->load('items')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'branch_id' => $branchId
            ]);
            return response()->json([
                'error' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }
} 