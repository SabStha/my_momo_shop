<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CashDrawer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\CashDrawerService;
use Illuminate\Support\Facades\Log;
// use App\Services\PrinterService;
use App\Models\CashDrawerLog;
use App\Models\CashDrawerSession;
use App\Services\ActivityLogService;
use App\Models\PaymentMethod;
use App\Models\Session;
use App\Models\Branch;

class PaymentController extends Controller
{
    protected $cashDrawerService;
    // protected $printerService;

    public function __construct(CashDrawerService $cashDrawerService)
    {
        $this->cashDrawerService = $cashDrawerService;
        // $this->printerService = $printerService;
    }

    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        if (!$branchId) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Please select a branch first.');
        }

        $branch = Branch::findOrFail($branchId);
        
        // Get active session
        $activeSession = Session::where('branch_id', $branchId)
            ->where('status', 'active')
            ->with(['openedBy'])
            ->first();

        // Get payment statistics
        $paymentStats = [
            'total_payments' => Payment::where('branch_id', $branchId)->count(),
            'today_revenue' => Payment::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->sum('amount'),
            'pending_payments' => Payment::where('branch_id', $branchId)
                ->where('status', 'pending')
                ->count(),
            'failed_payments' => Payment::where('branch_id', $branchId)
                ->where('status', 'failed')
                ->count(),
        ];

        // Get today's summary
        $todaySummary = [
            'total_sales' => Order::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->sum('total'),
            'total_orders' => Order::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->where('status', 'completed')
                ->count(),
            'total_payments' => Payment::where('branch_id', $branchId)
                ->whereDate('created_at', today())
                ->sum('amount')
        ];

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

        // Get all payment methods
        $paymentMethods = PaymentMethod::all();

        // Get recent payments
        $recentPayments = Payment::where('branch_id', $branchId)
            ->with(['order', 'method'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.payments.index', compact(
            'branch',
            'activeSession',
            'paymentStats',
            'paymentMethods',
            'recentPayments',
            'todaySummary',
            'posOrders',
            'onlineOrders',
            'orderHistory'
        ))->with('currentBranch', $branch);
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);
        
        return view('admin.payments.show', compact('payment'));
    }

    public function cancel(Payment $payment)
    {
        $this->authorize('cancel', $payment);

        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending payments can be cancelled.'
            ], 400);
        }

        $payment->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment cancelled successfully.'
        ]);
    }

    public function methods()
    {
        $methods = PaymentMethod::all();
        return view('admin.payments.methods', compact('methods'));
    }

    public function sessions()
    {
        $sessions = Payment::with(['user', 'method'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);
            
        return view('admin.payments.sessions', compact('sessions'));
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
        try {
            Log::info('Payment request received:', [
                'request_data' => $request->all(),
                'branch_id' => $request->branch_id,
                'payment_method' => $request->method
            ]);

            // Validate request
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'amount' => 'required|numeric|min:0',
                'method' => 'required|in:cash,card,mobile',
                'amount_received' => 'required_if:method,cash|numeric|min:0',
                'change_amount' => 'required_if:method,cash|numeric|min:0',
                'branch_id' => 'required|exists:branches,id'
            ]);

            DB::beginTransaction();

            // For cash payments, check if there's an active cash drawer session
            if ($request->method === 'cash') {
                Log::info('Checking cash drawer session for branch:', [
                    'branch_id' => $request->branch_id,
                    'date' => Carbon::today()->toDateString()
                ]);

                // Check for active session
                $session = CashDrawerSession::where('branch_id', $request->branch_id)
                    ->whereNull('closed_at')
                    ->first();

                Log::info('Cash drawer session check result:', [
                    'session_exists' => !is_null($session),
                    'session_data' => $session
                ]);

                if (!$session) {
                    throw new \Exception('Please open a cash drawer session before processing cash payments.');
                }

                // Get or create cash drawer
                $cashDrawer = CashDrawer::firstOrCreate(
                    ['branch_id' => $request->branch_id],
                    [
                        'total_cash' => 0,
                        'total_sales' => 0,
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
                    ]
                );
            }

            // Process payment
            $payment = Payment::create([
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'method' => $request->method,
                'status' => 'completed',
                'amount_received' => $request->amount_received,
                'change_amount' => $request->change_amount,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'branch_id' => $request->branch_id,
                'processed_by' => auth()->id()
            ]);

            Log::info('Payment created:', ['payment_id' => $payment->id]);

            // If payment is cash and successful, open drawer and print receipt
            if ($request->method === 'cash' && $payment->status === 'completed') {
                try {
                    // Update cash drawer balance
                    $cashDrawer->increment('total_cash', $request->amount);
                    $cashDrawer->increment('total_sales', $request->amount);

                    Log::info('Cash drawer balance updated:', [
                        'cash_drawer_id' => $cashDrawer->id,
                        'amount_added' => $request->amount
                    ]);

                    // Open drawer and print receipt (printerService temporarily disabled)
                    // $this->printerService->openDrawer();
                    // $this->printerService->printReceipt([
                    //     'order_id' => $payment->order_id,
                    //     'total' => $payment->amount,
                    //     'amount_received' => $payment->amount_received,
                    //     'change' => $payment->change_amount
                    // ]);
                    // Log drawer open event
                    CashDrawerLog::create([
                        'user_id' => auth()->id(),
                        'branch_id' => $request->branch_id,
                        'action' => 'open',
                        'reason' => 'cash_payment',
                        'status' => 'success',
                        'payment_id' => $payment->id
                    ]);

                    ActivityLogService::logPaymentActivity(
                        'cash_payment',
                        'Processed cash payment for order #' . $payment->order->order_number,
                        [
                            'payment_id' => $payment->id,
                            'order_id' => $payment->order_id,
                            'amount' => $payment->amount,
                            'cash_drawer_id' => $cashDrawer->id
                        ]
                    );

                } catch (\Exception $e) {
                    Log::error('Failed to open drawer or print receipt:', [
                        'error' => $e->getMessage(),
                        'payment_id' => $payment->id
                    ]);
                    
                    CashDrawerLog::create([
                        'user_id' => auth()->id(),
                        'branch_id' => $request->branch_id,
                        'action' => 'open',
                        'reason' => 'cash_payment',
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'payment_id' => $payment->id
                    ]);

                    ActivityLogService::logPaymentActivity(
                        'cash_payment_failed',
                        'Failed to process cash payment for order #' . $payment->order->order_number,
                        [
                            'payment_id' => $payment->id,
                            'order_id' => $payment->order_id,
                            'error' => $e->getMessage()
                        ]
                    );
                }
            } else {
                ActivityLogService::logPaymentActivity(
                    'payment',
                    'Processed ' . $request->method . ' payment for order #' . $payment->order->order_number,
                    [
                        'payment_id' => $payment->id,
                        'order_id' => $payment->order_id,
                        'amount' => $payment->amount,
                        'method' => $payment->method
                    ]
                );
            }

            DB::commit();
            return response()->json(['message' => 'Payment processed successfully']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment processing failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            ActivityLogService::logPaymentActivity(
                'payment_failed',
                'Failed to process payment',
                [
                    'error' => $e->getMessage(),
                    'request_data' => $request->all()
                ]
            );

            return response()->json(['message' => $e->getMessage()], 400);
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
        try {
            $request->validate([
                'branch_id' => 'required|exists:branches,id',
                'action' => 'required|in:open,close,adjust',
                'amount' => 'required|numeric',
                'reason' => 'required|string',
                'denominations' => 'required_if:action,close|array'
            ]);

            DB::beginTransaction();

            $cashDrawer = CashDrawer::where('branch_id', $request->branch_id)
                ->where('date', Carbon::today())
                ->first();

            if (!$cashDrawer) {
                $cashDrawer = CashDrawer::create([
                    'branch_id' => $request->branch_id,
                    'date' => Carbon::today(),
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

            $oldBalance = $cashDrawer->total_cash;

            switch ($request->action) {
                case 'open':
                    $cashDrawer->increment('total_cash', $request->amount);
                    $message = 'Cash drawer opened with initial amount of ' . $request->amount;
                    break;
                case 'close':
                    $cashDrawer->decrement('total_cash', $request->amount);
                    $cashDrawer->denominations = $request->denominations;
                    $message = 'Cash drawer closed with final amount of ' . $request->amount;
                    break;
                case 'adjust':
                    $cashDrawer->increment('total_cash', $request->amount);
                    $message = 'Cash drawer adjusted by ' . $request->amount;
                    break;
            }

            $cashDrawer->save();

            // Log the cash drawer action
            CashDrawerLog::create([
                'user_id' => auth()->id(),
                'branch_id' => $request->branch_id,
                'action' => $request->action,
                'reason' => $request->reason,
                'amount' => $request->amount,
                'old_balance' => $oldBalance,
                'new_balance' => $cashDrawer->total_cash,
                'status' => 'success'
            ]);

            ActivityLogService::logPaymentActivity(
                'cash_drawer_' . $request->action,
                $message,
                [
                    'cash_drawer_id' => $cashDrawer->id,
                    'old_balance' => $oldBalance,
                    'new_balance' => $cashDrawer->total_cash,
                    'amount' => $request->amount,
                    'reason' => $request->reason
                ]
            );

            DB::commit();
            return response()->json(['message' => $message]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cash drawer update failed:', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            ActivityLogService::logPaymentActivity(
                'cash_drawer_failed',
                'Failed to ' . $request->action . ' cash drawer',
                [
                    'error' => $e->getMessage(),
                    'request_data' => $request->all()
                ]
            );

            return response()->json(['message' => $e->getMessage()], 400);
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