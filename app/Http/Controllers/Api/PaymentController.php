<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use App\Models\CashDrawer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogService;
use App\Models\CashDrawerSession;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $branchId = $request->query('branch');
            if (!$branchId) {
                return response()->json(['error' => 'Branch ID is required'], 400);
            }

            // Get filter parameters
            $type = $request->query('type', 'all');
            $paymentStatus = $request->query('payment_status', 'all');
            $startDate = $request->query('start_date');
            $endDate = $request->query('end_date');
            $search = $request->query('search');
            $page = $request->query('page', 1);
            $perPage = $request->query('per_page', 10);

            // Build the query
            $query = Order::with(['user', 'items'])
                ->where('branch_id', $branchId);

            // Filter by type
            if ($type !== 'all') {
                if ($type === 'pos') {
                    $query->whereIn('order_type', ['dine_in', 'takeaway']);
                } else if ($type === 'online') {
                    $query->where('order_type', 'online');
                } else {
                    $query->where('order_type', $type);
                }
            }

            // Filter by payment status
            if ($paymentStatus !== 'all') {
                $query->where('payment_status', $paymentStatus);
            }

            // Filter by date range
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }

            // Search by order number or customer name
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('guest_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Get total count before pagination
            $total = $query->count();

            // Get paginated results
            $orders = $query->orderBy('created_at', 'desc')
                          ->skip(($page - 1) * $perPage)
                          ->take($perPage)
                          ->get();

            return response()->json([
                'items' => $orders,
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading orders: ' . $e->getMessage());
            return response()->json(['error' => 'Error loading orders'], 500);
        }
    }

    public function show($id)
    {
        try {
            $payment = Payment::with(['order', 'branch', 'createdBy', 'approvedBy'])->findOrFail($id);
            ActivityLogService::logPaymentActivity(
                'view',
                'User viewed payment details',
                [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'branch_id' => $payment->branch_id
                ]
            );
            return response()->json($payment);
        } catch (\Exception $e) {
            Log::error('Payment show error: ' . $e->getMessage());
            return response()->json(['message' => 'Payment not found'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,card,transfer',
                'reference_number' => 'nullable|string',
            ]);

            $order = Order::findOrFail($request->order_id);
            $branchId = $order->branch_id;

            DB::beginTransaction();

            $payment = Payment::create([
                'order_id' => $order->id,
                'branch_id' => $branchId,
                'user_id' => Auth::id(),
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'status' => 'completed',
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'paid_at' => now()
            ]);

            $order->update([
                'status' => 'completed',
                'payment_status' => 'paid'
            ]);

            ActivityLogService::logPaymentActivity(
                'create',
                'Payment processed for order #' . $order->id,
                [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method,
                    'branch_id' => $branchId
                ]
            );

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment,
                'order' => $order->load('payments')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to process payment: ' . $e->getMessage()], 500);
        }
    }

    public function getCashDrawer(Request $request)
    {
        try {
            $branchId = $request->query('branch_id');
            if (!$branchId) {
                return response()->json(['message' => 'Branch ID is required'], 400);
            }

            // Find the most recent cash drawer for this branch
            $cashDrawer = CashDrawer::where('branch_id', $branchId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$cashDrawer) {
                $cashDrawer = CashDrawer::create([
                    'branch_id' => $branchId,
                    'date' => now()->toDateString(),
                    'starting_amount' => 0,
                    'current_balance' => 0,
                    'total_cash' => 0,
                    'total_sales' => 0,
                    'status' => 'closed',
                    'denominations' => [
                        '1000' => 0,
                        '500' => 0,
                        '100' => 0,
                        '50' => 0,
                        '20' => 0,
                        '10' => 0,
                        '5' => 0,
                        '2' => 0,
                        '1' => 0
                    ]
                ]);
            }

            return response()->json($cashDrawer);
        } catch (\Exception $e) {
            Log::error('Cash drawer get error: ' . $e->getMessage());
            return response()->json(['message' => 'Error getting cash drawer'], 500);
        }
    }

    public function getCashDrawerBalance(Request $request)
    {
        try {
            $branchId = $request->query('branch_id');
            if (!$branchId) {
                return response()->json(['message' => 'Branch ID is required'], 400);
            }

            $cashDrawer = CashDrawer::where('branch_id', $branchId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$cashDrawer) {
                return response()->json([
                    'balance' => 0,
                    'minimum_balance' => config('cash_drawer.low_change_threshold', 1000),
                    'maximum_balance' => config('cash_drawer.excess_cash_threshold', 10000)
                ]);
            }

            // Calculate total cash from denominations
            $totalCash = 0;
            if (is_array($cashDrawer->denominations)) {
                foreach ($cashDrawer->denominations as $denomination => $count) {
                    $totalCash += (float)$denomination * (int)$count;
                }
            }

            // Update total_cash if it doesn't match
            if ($cashDrawer->total_cash != $totalCash) {
                $cashDrawer->total_cash = $totalCash;
                $cashDrawer->save();
            }

            return response()->json([
                'balance' => $totalCash,
                'minimum_balance' => config('cash_drawer.low_change_threshold', 1000),
                'maximum_balance' => config('cash_drawer.excess_cash_threshold', 10000)
            ]);
        } catch (\Exception $e) {
            Log::error('Cash drawer balance error: ' . $e->getMessage());
            return response()->json(['message' => 'Error getting cash drawer balance'], 500);
        }
    }

    public function updateCashDrawer(Request $request)
    {
        try {
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
                'denominations' => 'required|array'
            ]);

            $cashDrawer = CashDrawer::where('branch_id', $request->branch_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$cashDrawer) {
                $cashDrawer = new CashDrawer();
                $cashDrawer->branch_id = $request->branch_id;
                $cashDrawer->date = now()->toDateString();
                $cashDrawer->starting_amount = 0;
                $cashDrawer->current_balance = 0;
                $cashDrawer->total_cash = 0;
                $cashDrawer->total_sales = 0;
                $cashDrawer->status = 'closed';
            }

            $cashDrawer->denominations = $request->denominations;
            $cashDrawer->save();

            return response()->json([
                'message' => 'Cash drawer updated successfully',
                'cash_drawer' => $cashDrawer
            ]);
        } catch (\Exception $e) {
            Log::error('Cash drawer update error: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating cash drawer'], 500);
        }
    }

    public function getOrder($id)
    {
        try {
            $branchId = request()->header('X-Branch-ID');
            if (!$branchId) {
                return response()->json(['error' => 'Branch ID is required'], 400);
            }

            $order = Order::with(['items.product', 'user', 'table', 'payments'])
                ->where('branch_id', $branchId)
                ->findOrFail($id);

            return response()->json([
                'id' => $order->id,
                'order_number' => $order->order_number,
                'order_type' => $order->order_type,
                'total' => $order->total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'table' => $order->table,
                'user' => $order->user,
                'created_at' => $order->created_at,
                'items' => $order->items->map(function($item) {
                    return [
                        'id' => $item->id,
                        'product' => $item->product,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching order: ' . $e->getMessage());
            return response()->json(['error' => 'Order not found'], 404);
        }
    }

    public function processPayment(Request $request)
    {
        error_log('=== PAYMENT PROCESS STARTED ===');
        error_log('Request data: ' . json_encode($request->all()));

        try {
            $validated = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,card,mobile',
                'amount_received' => 'required_if:payment_method,cash|numeric|min:0',
                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            $order = Order::findOrFail($request->order_id);
            
            // Check if payment method is cash
            if ($request->payment_method === 'cash') {
                // Check for active cash drawer session
                $session = CashDrawerSession::where('branch_id', $order->branch_id)
                    ->whereNull('closed_at')
                    ->first();

                if (!$session) {
                    throw new \Exception('Please open a cash drawer session before processing cash payments.');
                }

                // Verify cash drawer has enough balance
                $cashDrawer = CashDrawer::where('branch_id', $order->branch_id)
                    ->whereDate('date', today())
                    ->first();

                if (!$cashDrawer) {
                    throw new \Exception('Cash drawer not initialized for today');
                }

                if ($request->amount_received < $order->total) {
                    throw new \Exception('Insufficient payment amount');
                }

                $change = $request->amount_received - $order->total;
                
                // Update cash drawer
                $cashDrawer->total_cash += $order->total;
                $cashDrawer->total_sales += $order->total;
                $cashDrawer->save();
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'branch_id' => $order->branch_id,
                'user_id' => $order->user_id,
                'amount' => $order->total,
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'reference' => 'PAY-' . strtoupper(uniqid()),
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'approved_by' => Auth::id(),
                'paid_at' => now()
            ]);

            $order->update([
                'status' => 'completed',
                'payment_status' => 'paid'
            ]);

            \Log::info('Order updated after payment', [
                'order_id' => $order->id,
                'order_type' => $order->order_type,
                'table_id' => $order->table_id,
                'status' => 'completed',
                'payment_status' => 'paid'
            ]);

            // Update table status if it's a dine-in or POS order
            if (($order->order_type === 'dine_in' || $order->order_type === 'pos') && $order->table_id) {
                \Log::info('Starting table status update process', [
                    'order_id' => $order->id,
                    'table_id' => $order->table_id,
                    'branch_id' => $order->branch_id,
                    'order_type' => $order->order_type,
                    'payment_status' => $payment->status,
                    'total_paid' => $request->amount,
                    'order_total' => $order->total
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
                'message' => 'Payment processed successfully',
                'payment' => $payment->load(['order', 'branch', 'createdBy', 'approvedBy'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment store error: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function update(Request $request, Payment $payment)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,completed,cancelled',
                'notes' => 'nullable|string'
            ]);

            $payment->update([
                'status' => $request->status,
                'notes' => $request->notes,
                'approved_by' => Auth::id()
            ]);

            ActivityLogService::logPaymentActivity(
                'update',
                'Payment status updated',
                [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'status' => $request->status,
                    'branch_id' => $payment->branch_id
                ]
            );

            return response()->json([
                'message' => 'Payment updated successfully',
                'payment' => $payment->load(['order', 'user'])
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update payment: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Payment $payment)
    {
        try {
            ActivityLogService::logPaymentActivity(
                'delete',
                'Payment cancelled',
                [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'branch_id' => $payment->branch_id
                ]
            );

            $payment->delete();

            return response()->json(['message' => 'Payment cancelled successfully']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to cancel payment: ' . $e->getMessage()], 500);
        }
    }
} 