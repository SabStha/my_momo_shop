<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Events\OrderPlaced;
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

            // Fire OrderPlaced event for badge progression and referral tracking
            event(new OrderPlaced($order));
            
            Log::info('OrderPlaced event fired for badge progression', [
                'order_id' => $order->id,
                'user_id' => $order->user_id ?? $order->created_by,
                'status' => $order->status
            ]);

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
        \Log::info('Processing payment for order', [
            'order_id' => $order->id,
            'order_type' => $order->order_type,
            'table_id' => $order->table_id,
            'request_data' => $request->all()
        ]);

        $request->validate([
            'payment_status' => 'required|in:paid,unpaid',
            'payment_method' => 'required|in:cash,card,qr',
            'amount_received' => 'required_if:payment_method,cash|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $order->update([
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'amount_received' => $request->amount_received,
                'change' => $request->payment_method === 'cash' ? $request->amount_received - $order->grand_total : 0,
                'status' => $request->payment_status === 'paid' ? 'completed' : $order->status,
            ]);

            \Log::info('Order updated after payment', [
                'order_id' => $order->id,
                'order_type' => $order->order_type,
                'table_id' => $order->table_id,
                'status' => $order->status,
                'payment_status' => $order->payment_status
            ]);

            // Update cash drawer if payment is cash
            if ($request->payment_method === 'cash' && $request->payment_status === 'paid') {
                $cashDrawer = \App\Models\CashDrawer::where('branch_id', $order->branch_id)
                    ->where('status', 'open')
                    ->latest()
                    ->first();

                if ($cashDrawer) {
                    \Log::info('Starting cash drawer update', [
                        'cash_drawer_id' => $cashDrawer->id,
                        'branch_id' => $order->branch_id,
                        'current_balance' => $cashDrawer->current_balance,
                        'amount_to_add' => $order->grand_total,
                        'current_denominations' => $cashDrawer->denominations
                    ]);

                    // Get current denominations
                    $denominations = $cashDrawer->denominations ?? [
                        '1000' => 0,
                        '500' => 0,
                        '100' => 0,
                        '50' => 0,
                        '20' => 0,
                        '10' => 0,
                        '5' => 0,
                        '1' => 0
                    ];

                    // Calculate denominations for the payment amount
                    $amount = $order->grand_total;
                    $paymentDenominations = [
                        '1000' => 0,
                        '500' => 0,
                        '100' => 0,
                        '50' => 0,
                        '20' => 0,
                        '10' => 0,
                        '5' => 0,
                        '1' => 0
                    ];

                    // Calculate denominations for the payment
                    $denominationValues = [1000, 500, 100, 50, 20, 10, 5, 1];
                    foreach ($denominationValues as $value) {
                        if ($amount >= $value) {
                            $count = floor($amount / $value);
                            $paymentDenominations[$value] = $count;
                            $amount -= ($count * $value);
                        }
                    }

                    // Update denominations
                    foreach ($denominationValues as $value) {
                        $denominations[$value] = ($denominations[$value] ?? 0) + $paymentDenominations[$value];
                    }

                    \Log::info('Calculated denominations for payment', [
                        'payment_amount' => $order->grand_total,
                        'payment_denominations' => $paymentDenominations,
                        'updated_denominations' => $denominations
                    ]);

                    try {
                        DB::beginTransaction();

                        $cashDrawer->update([
                            'total_cash' => $cashDrawer->total_cash + $order->grand_total,
                            'current_balance' => $cashDrawer->current_balance + $order->grand_total,
                            'total_sales' => $cashDrawer->total_sales + $order->grand_total,
                            'denominations' => $denominations
                        ]);

                        DB::commit();

                        \Log::info('Cash drawer updated successfully', [
                            'cash_drawer_id' => $cashDrawer->id,
                            'new_balance' => $cashDrawer->total_cash,
                            'new_current_balance' => $cashDrawer->current_balance,
                            'new_total_sales' => $cashDrawer->total_sales,
                            'new_denominations' => $denominations
                        ]);

                        // Refresh the cash drawer to get updated values
                        $cashDrawer->refresh();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        \Log::error('Failed to update cash drawer', [
                            'error' => $e->getMessage(),
                            'cash_drawer_id' => $cashDrawer->id,
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
                } else {
                    \Log::warning('No active cash drawer found for branch', [
                        'branch_id' => $order->branch_id
                    ]);
                }
            }

            // Update table status if it's a dine-in or POS order
            if (($order->order_type === 'dine_in' || $order->order_type === 'pos') && $order->table_id) {
                \Log::info('Starting table status update process', [
                    'order_id' => $order->id,
                    'table_id' => $order->table_id,
                    'branch_id' => $order->branch_id,
                    'order_type' => $order->order_type
                ]);

                $table = \App\Models\Table::where('id', $order->table_id)
                    ->where('branch_id', $order->branch_id)
                    ->first();

                \Log::info('Table lookup result', [
                    'table_found' => (bool)$table,
                    'table_id' => $order->table_id,
                    'branch_id' => $order->branch_id,
                    'current_status' => $table ? $table->status : null,
                    'current_occupied' => $table ? $table->is_occupied : null
                ]);

                if ($table) {
                    try {
                        \Log::info('Attempting to update table status', [
                            'table_id' => $table->id,
                            'current_status' => $table->status,
                            'current_occupied' => $table->is_occupied,
                            'target_status' => 'available',
                            'target_occupied' => false
                        ]);

                        $updated = $table->updateStatus('available', false);
                        
                        \Log::info('Table update result', [
                            'update_success' => $updated,
                            'table_id' => $table->id,
                            'new_status' => $table->status,
                            'new_occupied' => $table->is_occupied
                        ]);

                        if (!$updated) {
                            \Log::error('Failed to update table status after payment', [
                                'table_id' => $table->id,
                                'branch_id' => $table->branch_id,
                                'order_id' => $order->id,
                                'current_status' => $table->status,
                                'current_occupied' => $table->is_occupied,
                                'timestamp' => now()
                            ]);
                            throw new \Exception('Failed to update table status');
                        }

                        \Log::info('Table status updated successfully after payment', [
                            'table_id' => $table->id,
                            'branch_id' => $table->branch_id,
                            'order_id' => $order->id,
                            'old_status' => $table->getOriginal('status'),
                            'new_status' => 'available',
                            'old_occupied' => $table->getOriginal('is_occupied'),
                            'new_occupied' => false,
                            'timestamp' => now()
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Exception during table update', [
                            'error' => $e->getMessage(),
                            'table_id' => $table->id,
                            'order_id' => $order->id,
                            'trace' => $e->getTraceAsString()
                        ]);
                        throw $e;
                    }
                } else {
                    \Log::warning('Table not found or branch mismatch', [
                        'table_id' => $order->table_id,
                        'branch_id' => $order->branch_id,
                        'order_id' => $order->id,
                        'timestamp' => now()
                    ]);
                }
            } else {
                \Log::info('Skipping table update - not a dine-in/POS order or no table assigned', [
                    'order_id' => $order->id,
                    'order_type' => $order->order_type,
                    'table_id' => $order->table_id
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'order' => $order->load('items.product', 'table')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error processing payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order status (for mobile app)
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Validate that the user can only update their own orders
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this order'
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        // Send push notification and in-app notification if status changed
        if ($oldStatus !== $validated['status']) {
            $notificationService = app(\App\Services\OrderNotificationService::class);
            $notificationService->sendOrderStatusNotification($order, $validated['status'], $oldStatus);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'order' => $order->load('items.product')
        ]);
    }
} 