<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CashDrawer;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch', 1);

        // Get cash drawer status
        $cashDrawer = CashDrawer::where('branch_id', $branchId)
            ->whereDate('created_at', Carbon::today())
            ->first();

        // Get online orders
        $onlineOrders = Order::where('order_type', 'online')
            ->where('payment_status', '!=', 'paid')
            ->where('branch_id', $branchId)
            ->with(['items.product', 'user'])
            ->latest()
            ->get();

        // Get POS orders (dine-in and takeaway)
        $posOrders = Order::whereIn('order_type', ['dine_in', 'takeaway'])
            ->where('payment_status', '!=', 'paid')
            ->where('branch_id', $branchId)
            ->with(['items.product', 'user', 'table'])
            ->latest()
            ->get();

        // Get order history (completed orders)
        $orderHistory = Order::where('payment_status', 'paid')
            ->where('branch_id', $branchId)
            ->with(['items.product', 'user', 'table'])
            ->latest()
            ->paginate(20);

        // Get today's summary
        $todaySummary = [
            'total_sales' => Order::where('branch_id', $branchId)
                ->whereDate('created_at', Carbon::today())
                ->where('status', 'completed')
                ->sum('total'),
            'total_orders' => Order::where('branch_id', $branchId)
                ->whereDate('created_at', Carbon::today())
                ->where('status', 'completed')
                ->count(),
            'total_payments' => Payment::where('branch_id', $branchId)
                ->whereDate('created_at', Carbon::today())
                ->sum('amount')
        ];

        return view('admin.payment-manager.index', compact(
            'cashDrawer',
            'onlineOrders',
            'posOrders',
            'orderHistory',
            'todaySummary'
        ));
    }

    public function showOrder(Order $order)
    {
        $order->load(['items.product', 'user', 'table', 'payments']);
        return view('admin.payment-manager.order-details', compact('order'));
    }

    public function processPayment(Request $request, Order $order)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,wallet',
            'reference_number' => 'required_if:payment_method,card'
        ]);

        try {
            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'branch_id' => $order->branch_id
            ]);

            // Update order status
            $order->status = 'completed';
            $order->payment_status = 'paid';
            $order->save();

            // Update table status if it's a dine-in order
            if ($order->order_type === 'dine_in' && $order->table_id) {
                \Log::info('Attempting to update table status after payment', [
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

                if ($table) {
                    $updated = $table->update([
                        'status' => 'available',
                        'is_occupied' => false
                    ]);
                    
                    if (!$updated) {
                        \Log::error('Failed to update table status after payment', [
                            'table_id' => $table->id,
                            'branch_id' => $table->branch_id,
                            'order_id' => $order->id,
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
                } else {
                    \Log::warning('Table not found or branch mismatch', [
                        'table_id' => $order->table_id,
                        'branch_id' => $order->branch_id,
                        'order_id' => $order->id,
                        'timestamp' => now()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'order' => $order->load(['items.product', 'table'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
                'table_id' => $order->table_id,
                'branch_id' => $order->branch_id,
                'timestamp' => now()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function openCashDrawer(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0'
        ]);

        try {
            $cashDrawer = CashDrawer::create([
                'branch_id' => $request->branch_id,
                'opening_balance' => $request->opening_balance,
                'current_balance' => $request->opening_balance,
                'date' => Carbon::today(),
                'opened_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cash drawer opened successfully',
                'cash_drawer' => $cashDrawer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to open cash drawer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function closeCashDrawer(Request $request)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0'
        ]);

        try {
            $cashDrawer = CashDrawer::where('branch_id', $request->branch_id)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if (!$cashDrawer) {
                throw new \Exception('No open cash drawer found');
            }

            $cashDrawer->update([
                'closing_balance' => $request->closing_balance,
                'closed_by' => auth()->id(),
                'closed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cash drawer closed successfully',
                'cash_drawer' => $cashDrawer
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close cash drawer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCashDrawerStatus(Request $request)
    {
        $cashDrawer = CashDrawer::where('branch_id', $request->branch_id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        return response()->json([
            'success' => true,
            'cash_drawer' => $cashDrawer
        ]);
    }
} 