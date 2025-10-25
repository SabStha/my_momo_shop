<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = Branch::findOrFail($branchId);

        // POS Orders: order_type = 'dine_in' or 'takeaway'
        $posOrdersPaid = Order::where('branch_id', $branchId)
            ->whereIn('order_type', ['dine_in', 'takeaway'])
            ->where('payment_status', 'paid')
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
        $posOrdersUnpaid = Order::where('branch_id', $branchId)
            ->whereIn('order_type', ['dine_in', 'takeaway'])
            ->where('payment_status', '!=', 'paid')
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Online Orders: order_type = 'online'
        $onlineOrdersPaid = Order::where('branch_id', $branchId)
            ->where('order_type', 'online')
            ->where('payment_status', 'paid')
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
        $onlineOrdersUnpaid = Order::where('branch_id', $branchId)
            ->where('order_type', 'online')
            ->where('payment_status', '!=', 'paid')
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Order History: all orders, paginated
        $orderHistory = Order::where('branch_id', $branchId)
            ->with(['user', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Debug information
        \Log::info('Orders fetched:', [
            'pos_paid_count' => $posOrdersPaid->count(),
            'pos_unpaid_count' => $posOrdersUnpaid->count(),
            'online_paid_count' => $onlineOrdersPaid->count(),
            'online_unpaid_count' => $onlineOrdersUnpaid->count(),
            'history_count' => $orderHistory->count()
        ]);

        return view('admin.orders.index', compact(
            'branch',
            'posOrdersPaid', 'posOrdersUnpaid',
            'onlineOrdersPaid', 'onlineOrdersUnpaid',
            'orderHistory'
        ));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'branch']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->update(['status' => $validated['status']]);

        // Send push notification and in-app notification if status changed
        if ($oldStatus !== $validated['status'] && $order->user_id) {
            $notificationService = app(\App\Services\OrderNotificationService::class);
            $notificationService->sendOrderStatusNotification($order, $validated['status'], $oldStatus);
        }

        return redirect()
            ->route('admin.orders.index', ['branch' => $order->branch_id])
            ->with('success', 'Order status updated successfully.');
    }

    public function destroy(Order $order)
    {
        try {
            $branchId = $order->branch_id;
            $order->delete();
            
            return redirect()
                ->route('admin.orders.index', ['branch' => $branchId])
                ->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Error deleting order. Please try again.');
        }
    }

    public function getOrdersJson(Request $request)
    {
        try {
            $branchId = $request->query('branch');
            
            if (!$branchId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Branch ID is required'
                ], 400);
            }

            // Get all orders (including completed/paid orders for payment manager)
            $orders = Order::where('branch_id', $branchId)
                ->with(['items.product', 'table', 'user'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Format orders for JSON response
            $formattedOrders = $orders->map(function ($order) {
                // Calculate total from items if total_amount is empty
                $totalAmount = $order->total_amount;
                if (empty($totalAmount) || $totalAmount == 0) {
                    $totalAmount = $order->items->sum(function ($item) {
                        return $item->quantity * $item->price;
                    });
                }
                
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'type' => $order->order_type,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'total_amount' => (float) $totalAmount,
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
            });

            \Log::info('Orders fetched for payment manager', [
                'branch_id' => $branchId,
                'count' => $orders->count(),
                'order_ids' => $orders->pluck('id')->toArray()
            ]);

            return response()->json([
                'success' => true,
                'orders' => $formattedOrders
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading orders for payment manager: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load orders'
            ], 500);
        }
    }

    public function processPayment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'payment_method' => 'required|in:cash,card,mobile,wallet,khalti',
                'amount' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'reference_number' => 'nullable|string',
                'cash_denominations' => 'nullable|array',
                'amount_received' => 'nullable|numeric|min:0',
                'wallet_number' => 'nullable|string|required_if:payment_method,wallet',
                'transaction_id' => 'nullable|string|required_if:payment_method,khalti'
            ]);

            $order = Order::findOrFail($request->order_id);
            
            // Check if order is already paid
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This order has already been paid'
                ], 400);
            }

            DB::beginTransaction();

            // Update order payment status
            $order->update([
                'payment_status' => 'paid',
                'payment_method' => $request->payment_method,
                'amount_received' => $request->amount_received ?? $request->amount,
                'change' => ($request->amount_received ?? $request->amount) - $request->amount,
                'status' => 'completed'
            ]);

            // Set table as available if order has a table
            if ($order->table_id) {
                $table = \App\Models\Table::find($order->table_id);
                if ($table) {
                    $table->updateStatus('available', false);
                }
            }

            // Create payment record
            $payment = \App\Models\Payment::create([
                'order_id' => $order->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'branch_id' => $order->branch_id
            ]);

            // Handle cash denominations if cash payment
            if ($request->payment_method === 'cash' && $request->cash_denominations) {
                // Update cash drawer denominations
                $this->updateCashDrawerDenominations($order->branch_id, $request->cash_denominations);
            }

            DB::commit();

            \Log::info('Payment processed successfully', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount' => $request->amount,
                'method' => $request->payment_method,
                'processed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment' => $payment,
                'order' => $order->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment processing failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateCashDrawerDenominations($branchId, $cashDenominations)
    {
        try {
            // Get current cash drawer
            $cashDrawer = \App\Models\CashDrawer::where('branch_id', $branchId)->first();
            
            if ($cashDrawer) {
                // Add received denominations to current denominations
                $currentDenominations = $cashDrawer->denominations ?? [];
                
                foreach ($cashDenominations as $denomination => $count) {
                    if (!isset($currentDenominations[$denomination])) {
                        $currentDenominations[$denomination] = 0;
                    }
                    $currentDenominations[$denomination] += $count;
                }
                
                $cashDrawer->denominations = $currentDenominations;
                $cashDrawer->save();
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update cash drawer denominations: ' . $e->getMessage());
        }
    }

    /**
     * Accept an online order
     */
    public function acceptOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            // Check if order is online and pending
            if ($order->order_type !== 'online' || $order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be accepted'
                ], 400);
            }
            
            // Update order status to confirmed, then immediately to preparing
            $order->status = 'confirmed';
            $order->confirmed_at = now();
            $order->save();
            
            // Immediately start preparing
            $order->status = 'preparing';
            $order->preparing_started_at = now();
            $order->save();
            
            \Log::info('Order accepted and preparing started', [
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'user_id' => auth()->id(),
                'status' => 'preparing'
            ]);
            
            // Send notification to customer (if mobile app)
            if ($order->user_id) {
                try {
                    $mobileNotificationService = app(\App\Services\MobileNotificationService::class);
                    $mobileNotificationService->sendOrderUpdate(
                        $order->user,
                        $order,
                        'preparing'
                    );
                } catch (\Exception $e) {
                    \Log::warning('Failed to send mobile notification: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order accepted and preparing started',
                'order' => $order->fresh()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to accept order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept order'
            ], 500);
        }
    }

    /**
     * Mark order as ready for delivery/pickup
     */
    public function markAsReady(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            // Update order status to ready
            $order->status = 'ready';
            $order->ready_at = now();
            $order->save();
            
            \Log::info('Order marked as ready', [
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'user_id' => auth()->id()
            ]);
            
            // Send notification to customer
            if ($order->user_id) {
                try {
                    $mobileNotificationService = app(\App\Services\MobileNotificationService::class);
                    $mobileNotificationService->sendOrderUpdate(
                        $order->user,
                        $order,
                        'ready'
                    );
                } catch (\Exception $e) {
                    \Log::warning('Failed to send mobile notification: ' . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Order marked as ready',
                'order' => $order->fresh()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to mark order as ready: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark order as ready'
            ], 500);
        }
    }

    /**
     * Decline an online order
     */
    public function declineOrder(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            // Check if order is online and pending
            if ($order->order_type !== 'online' || $order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order cannot be declined'
                ], 400);
            }
            
            // Update order status to declined
            $order->status = 'declined';
            $order->save();
            
            \Log::info('Order declined', [
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Order declined successfully'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to decline order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to decline order'
            ], 500);
        }
    }

    /**
     * Reset order status to pending (for re-accept/decline)
     */
    public function resetOrderStatus(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);
            
            // Check if order is online and can be reset
            if ($order->order_type !== 'online') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only online orders can be reset to pending'
                ], 400);
            }
            
            // Check if order is in a state that can be reset
            if (!in_array($order->status, ['confirmed', 'declined'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order status cannot be reset from current state: ' . $order->status
                ], 400);
            }
            
            // Reset order status to pending
            $order->status = 'pending';
            $order->save();
            
            \Log::info('Order status reset to pending', [
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'previous_status' => $request->input('previous_status'),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Order status reset to pending successfully',
                'order' => $order->fresh()
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to reset order status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset order status'
            ], 500);
        }
    }

    /**
     * Print kitchen order
     */
    public function kitchenPrint($orderId)
    {
        try {
            $order = Order::with(['items.product', 'user'])
                ->findOrFail($orderId);
            
            return view('admin.orders.kitchen-print', compact('order'));
            
        } catch (\Exception $e) {
            \Log::error('Failed to load kitchen print: ' . $e->getMessage());
            abort(404, 'Order not found');
        }
    }
} 