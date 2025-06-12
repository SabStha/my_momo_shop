<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CashDrawer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index()
    {
        return view('admin.payments.index');
    }

    public function getPayments(Request $request)
    {
        $query = Payment::query()
            ->with(['order', 'processedBy'])
            ->where('branch_id', session('selected_branch_id'));

        // Apply filters
        if ($request->filled('startDate')) {
            $query->whereDate('created_at', '>=', $request->startDate);
        }
        if ($request->filled('endDate')) {
            $query->whereDate('created_at', '<=', $request->endDate);
        }
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('order', function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%");
                })
                ->orWhere('amount', 'like', "%{$search}%");
            });
        }

        // Get data based on type
        switch ($request->type) {
            case 'online':
                $query->whereHas('order', function($q) {
                    $q->where('order_type', 'online');
                });
                break;
            case 'pos':
                $query->whereHas('order', function($q) {
                    $q->where('order_type', 'pos');
                });
                break;
            case 'history':
                $query->where('status', 'completed');
                break;
        }

        $total = $query->count();
        $items = $query->orderBy('created_at', 'desc')
            ->skip(($request->page - 1) * $request->perPage)
            ->take($request->perPage)
            ->get();

        return response()->json([
            'items' => $items,
            'total' => $total,
            'start' => ($request->page - 1) * $request->perPage + 1,
            'end' => min($request->page * $request->perPage, $total)
        ]);
    }

    public function getOrder($id)
    {
        $order = Order::with(['customer', 'items.product'])
            ->where('branch_id', session('selected_branch_id'))
            ->findOrFail($id);

        return response()->json($order);
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'method' => 'required|in:cash,card,mobile',
            'amount_received' => 'required_if:method,cash|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $order = Order::where('branch_id', session('selected_branch_id'))
                ->findOrFail($request->order_id);

            // Check if payment already exists
            if ($order->payment) {
                throw new \Exception('Payment already exists for this order');
            }

            // Create payment record
            $payment = new Payment([
                'order_id' => $order->id,
                'branch_id' => session('selected_branch_id'),
                'amount' => $request->amount,
                'method' => $request->method,
                'status' => 'completed',
                'notes' => $request->notes,
                'processed_by' => auth()->id()
            ]);

            // Handle cash payments
            if ($request->method === 'cash') {
                $change = $request->amount_received - $request->amount;
                if ($change < 0) {
                    throw new \Exception('Insufficient payment amount');
                }

                // Update cash drawer
                $cashDrawer = CashDrawer::where('branch_id', session('selected_branch_id'))
                    ->where('date', Carbon::today())
                    ->first();

                if (!$cashDrawer) {
                    throw new \Exception('Cash drawer not initialized for today');
                }

                $cashDrawer->increment('total_cash', $request->amount);
                $cashDrawer->increment('total_sales', $request->amount);
                $payment->change_amount = $change;
            }

            $payment->save();

            // Update order status
            $order->status = 'completed';
            $order->save();

            DB::commit();

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getCashDrawer()
    {
        $cashDrawer = CashDrawer::where('branch_id', session('selected_branch_id'))
            ->where('date', Carbon::today())
            ->first();

        if (!$cashDrawer) {
            return response()->json([
                'message' => 'Cash drawer not initialized for today'
            ], 404);
        }

        return response()->json($cashDrawer);
    }

    public function getCashDrawerBalance()
    {
        $cashDrawer = CashDrawer::where('branch_id', session('selected_branch_id'))
            ->where('date', Carbon::today())
            ->first();

        if (!$cashDrawer) {
            return response()->json([
                'message' => 'Cash drawer not initialized for today'
            ], 404);
        }

        return response()->json([
            'balance' => $cashDrawer->total_cash,
            'minimum_balance' => 1000 // Configurable minimum balance
        ]);
    }

    public function updateCashDrawer(Request $request)
    {
        $request->validate([
            'starting_amount' => 'required|numeric|min:0',
            'denominations' => 'required|array'
        ]);

        DB::beginTransaction();
        try {
            $cashDrawer = CashDrawer::where('branch_id', session('selected_branch_id'))
                ->where('date', Carbon::today())
                ->first();

            if (!$cashDrawer) {
                $cashDrawer = new CashDrawer([
                    'branch_id' => session('selected_branch_id'),
                    'date' => Carbon::today(),
                    'starting_amount' => $request->starting_amount,
                    'total_cash' => $request->starting_amount,
                    'total_sales' => 0,
                    'denominations' => $request->denominations
                ]);
            } else {
                $cashDrawer->starting_amount = $request->starting_amount;
                $cashDrawer->denominations = $request->denominations;
                
                // Recalculate total cash based on denominations
                $totalCash = 0;
                foreach ($request->denominations as $denomination => $count) {
                    $totalCash += $denomination * $count;
                }
                $cashDrawer->total_cash = $totalCash;
            }

            $cashDrawer->save();
            DB::commit();

            return response()->json([
                'message' => 'Cash drawer updated successfully',
                'cash_drawer' => $cashDrawer
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function viewPayment($id)
    {
        $payment = Payment::with(['order', 'processedBy'])
            ->where('branch_id', session('selected_branch_id'))
            ->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    public function printReceipt($id)
    {
        $payment = Payment::with(['order', 'processedBy'])
            ->where('branch_id', session('selected_branch_id'))
            ->findOrFail($id);

        return view('admin.payments.receipt', compact('payment'));
    }
} 