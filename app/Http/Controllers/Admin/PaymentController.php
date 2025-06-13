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
use App\Services\PrinterService;
use App\Models\CashDrawerLog;
use App\Models\CashDrawerSession;

class PaymentController extends Controller
{
    protected $cashDrawerService;
    protected $printerService;

    public function __construct(CashDrawerService $cashDrawerService, PrinterService $printerService)
    {
        $this->cashDrawerService = $cashDrawerService;
        $this->printerService = $printerService;
    }

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
        try {
            Log::info('Payment request received:', [
                'request_data' => $request->all(),
                'branch_id' => $request->branch_id,
                'payment_method' => $request->payment_method
            ]);

            // Validate request
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,card,mobile',
                'amount_received' => 'required_if:payment_method,cash|numeric|min:0',
                'change_amount' => 'required_if:payment_method,cash|numeric|min:0',
                'branch_id' => 'required|exists:branches,id'
            ]);

            DB::beginTransaction();

            // For cash payments, check if there's an active cash drawer session
            if ($request->payment_method === 'cash') {
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
                            '4' => 0,
                            '1' => 0
                        ]
                    ]
                );
            }

            // Process payment
            $payment = Payment::create([
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
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
            if ($request->payment_method === 'cash' && $payment->status === 'completed') {
                try {
                    // Update cash drawer balance
                    $cashDrawer->increment('total_cash', $request->amount);
                    $cashDrawer->increment('total_sales', $request->amount);

                    Log::info('Cash drawer balance updated:', [
                        'cash_drawer_id' => $cashDrawer->id,
                        'amount_added' => $request->amount
                    ]);

                    // Open drawer
                    $this->printerService->openDrawer();

                    // Log drawer open event
                    CashDrawerLog::create([
                        'user_id' => auth()->id(),
                        'branch_id' => $request->branch_id,
                        'action' => 'open',
                        'reason' => 'cash_payment',
                        'status' => 'success',
                        'payment_id' => $payment->id
                    ]);

                    // Print receipt
                    $this->printerService->printReceipt([
                        'order_id' => $payment->order_id,
                        'total' => $payment->amount,
                        'amount_received' => $payment->amount_received,
                        'change' => $payment->change_amount
                    ]);

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
                }
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