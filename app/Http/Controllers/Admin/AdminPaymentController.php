<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CashDrawer;
use App\Models\Table;
use App\Models\CashDrawerSession;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Events\PaymentMethodSelected;
use App\Events\PaymentCompleted;
use App\Services\Payment\PaymentService;

class AdminPaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    public function index(Request $request)
    {
        $branchId = $request->query('branch', 1);

        // Set branch ID in session
        session(['selected_branch_id' => $branchId]);

        // Get cash drawer status — no date filter, drawer carries over until explicitly closed
        $cashDrawer = CashDrawer::where('branch_id', $branchId)
            ->where('status', 'open')
            ->latest()
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

        return view('admin.payments.index', compact(
            'onlineOrders',
            'posOrders', 
            'orderHistory',
            'todaySummary',
            'cashDrawer'
        ));
    }

    public function showOrder(Order $order)
    {
        $order->load(['items.product', 'user', 'table', 'payments']);
        return view('admin.payments.show', compact('order'));
    }

    public function processPayment(Request $request, Order $order)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,card,wallet,cod',
                'amount_received' => 'required_if:payment_method,cash|numeric|min:0',
                'change_amount' => 'required_if:payment_method,cash|numeric|min:0',
                'branch_id' => 'required|exists:branches,id'
            ]);

            DB::beginTransaction();

            // Create payment record in pending status
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'currency' => 'INR',
                'status' => 'pending',
                'transaction_id' => $request->reference_number,
                'branch_id' => $order->branch_id,
                'payment_details' => [
                    'payment_method' => $request->payment_method,
                    'amount_received' => $request->amount_received,
                    'change_amount' => $request->change_amount,
                    'processed_by' => auth()->id(),
                    'branch_id' => $order->branch_id
                ],
            ]);

            // Process via service
            // The CashPaymentProcessor/WalletPaymentProcessor handle specific logic now
            $response = $this->paymentService->process($payment);

            if (!$response->success) {
                throw new \Exception($response->message);
            }

            // Always update the order after payment — the processor may not do it
            // (CardPaymentProcessor does not update the order; CashPaymentProcessor does,
            //  but the ServiceProvider binding is broken so CashPaymentProcessor is never
            //  resolved via PaymentService. Explicit update here fixes that gap.)
            $order->update([
                'payment_status' => 'paid',
                'status'         => 'completed',
            ]);

            \Log::info('Payment processed', [
                'order_id'       => $order->id,
                'payment_status' => $order->fresh()->payment_status,
                'status'         => $order->fresh()->status,
            ]);

            // Update table status if it's a dine-in order — mark needs cleaning
            if ($order->table_id) {
                $table = Table::where('id', $order->table_id)
                    ->where('branch_id', $order->branch_id)
                    ->first();

                if ($table) {
                    $table->update([
                        'status'           => 'needs_cleaning',
                        'is_occupied'      => false,
                        'current_order_id' => null,
                    ]);
                }
            }

            DB::commit();

            // Broadcast payment completion to customer viewer via Pusher
            try {
                broadcast(new PaymentCompleted(
                    $order->id,
                    $request->payment_method,
                    (float) $order->total_amount,
                    (int) $order->branch_id
                ));
            } catch (\Throwable $e) {
                Log::warning('PaymentCompleted broadcast failed', ['error' => $e->getMessage()]);
            }

            // Update cash drawer balance for cash/cod payments
            if (in_array($request->payment_method, ['cash', 'cod'])) {
                $activeDrawer = CashDrawer::where('branch_id', $order->branch_id)
                    ->where('status', 'open')
                    ->latest()
                    ->first();
                if ($activeDrawer) {
                    $activeDrawer->increment('current_balance', $request->amount);
                    $activeDrawer->increment('total_cash', $request->amount);
                    $activeDrawer->increment('total_sales', $request->amount);
                    \Log::info('Cash drawer updated', [
                        'drawer_id'   => $activeDrawer->id,
                        'added'       => $request->amount,
                        'method'      => $request->payment_method,
                        'new_balance' => $activeDrawer->fresh()->current_balance,
                    ]);
                } else {
                    \Log::warning('Cash drawer not found for branch', ['branch_id' => $order->branch_id]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'order' => $order->refresh()->load(['items.product', 'table'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment processing failed', [
                'error' => $e->getMessage(),
                'order_id' => $order->id,
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
                'date' => Carbon::today(),
                'starting_amount' => $request->opening_balance,
                'current_balance' => $request->opening_balance,
                'total_cash' => $request->opening_balance,
                'total_sales' => 0,
                'status' => 'open',
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
                ->whereDate('date', Carbon::today())
                ->first();

            if (!$cashDrawer) {
                throw new \Exception('No open cash drawer found');
            }

            $cashDrawer->update([
                'current_balance' => $request->closing_balance,
                'total_cash' => $request->closing_balance,
                'status' => 'closed'
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
        // Accept both ?branch= and ?branch_id= for compatibility with different callers
        $branchId = $request->query('branch_id', $request->query('branch', session('selected_branch_id')));

        $cashDrawer = CashDrawer::where('branch_id', $branchId)
            ->where('status', 'open')
            ->latest()
            ->first();

        return response()->json([
            'is_open'       => $cashDrawer ? true : false,
            'total_balance' => $cashDrawer ? (float) $cashDrawer->current_balance : 0,
            'total_cash'    => $cashDrawer ? (float) $cashDrawer->current_balance : 0,
        ]);
    }

    public function getWalletBalance(Order $order)
    {
        if (!$order->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'This order is not associated with a registered user.'
            ], 400);
        }

        $user = $order->user;
        $wallet = $user->wallet;
        
        if (!$wallet) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have a wallet.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'balance' => $wallet->balance
        ]);
    }

    /**
     * Get wallet balance by wallet number
     */
    public function getWalletBalanceByNumber($walletNumber)
    {
        try {
            // Get branch ID from request header
            $branchId = request()->header('X-Branch-ID');
            if (!$branchId) {
                return response()->json(['error' => 'Branch ID is required'], 400);
            }

            // First try to find the wallet
            $wallet = Wallet::where('wallet_number', $walletNumber)
                          ->where('branch_id', $branchId)
                          ->first();

            // If wallet doesn't exist, try to find user by wallet number
            if (!$wallet) {
                // Try to find user by wallet number
                $user = User::whereHas('wallet', function($query) use ($walletNumber) {
                    $query->where('wallet_number', $walletNumber);
                })->first();
                
                if (!$user) {
                    return response()->json(['error' => 'Wallet not found'], 404);
                }

                // Create new wallet for user
                $wallet = Wallet::create([
                    'user_id' => $user->id,
                    'branch_id' => $branchId,
                    'wallet_number' => $walletNumber,
                    'balance' => 0,
                    'is_active' => true
                ]);
            }

            return response()->json([
                'wallet_number' => $wallet->wallet_number,
                'balance' => $wallet->balance,
                'user_name' => $wallet->user ? $wallet->user->name : 'N/A'
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching wallet balance: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch wallet balance'], 500);
        }
    }

    /**
     * Process a payment
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'order_id' => 'required|exists:orders,id',
                'payment_method' => 'required|in:cash,card,wallet',
                'amount' => 'required|numeric|min:0',
                'amount_received' => 'required_if:payment_method,cash|numeric|min:0',
                'notes' => 'nullable|string',
            ];
            // Only require reference_number for card, otherwise nullable
            if ($request->payment_method === 'card') {
                $rules['reference_number'] = 'required|string';
            } else {
                $rules['reference_number'] = 'nullable|string';
            }
            // Only require wallet_number for wallet, otherwise nullable
            if ($request->payment_method === 'wallet') {
                $rules['wallet_number'] = 'required|string';
            } else {
                $rules['wallet_number'] = 'nullable|string';
            }
            $request->validate($rules);

            $order = Order::findOrFail($request->order_id);
            $branchId = $request->header('X-Branch-ID');

            if (!$branchId) {
                return response()->json(['message' => 'Branch ID is required'], 400);
            }

            \Log::info('Payment request data', $request->all());
            if (empty($request->payment_method)) {
                throw new \Exception('Payment method is missing from the request.');
            }
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'wallet_number' => $request->wallet_number,
                'notes' => $request->notes,
                'branch_id' => $branchId,
                'status' => 'pending'
            ]);

            // Process via service
            // The service handles order status, cash drawer, and wallet increments/decrements
            $response = $this->paymentService->process($payment);

            if (!$response->success) {
                throw new \Exception($response->message);
            }

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment->refresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json(['message' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Broadcast the cashier's payment method selection to the customer viewer.
     */
    public function broadcastMethod(Request $request)
    {
        $request->validate([
            'order_id'  => 'required|integer',
            'method'    => 'required|string',
            'amount'    => 'required|numeric',
            'branch_id' => 'required|integer',
        ]);

        try {
            broadcast(new PaymentMethodSelected(
                (int)   $request->order_id,
                        $request->method,
                (float) $request->amount,
                (int)   $request->branch_id
            ));
        } catch (\Throwable $e) {
            Log::warning('PaymentMethodSelected broadcast failed', ['error' => $e->getMessage()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Return bank account details for the payment popup.
     */
    public function getPaymentInfo()
    {
        return response()->json([
            'bank_name'         => Setting::getValue('bank_name', 'Nepal Bank'),
            'bank_account'      => Setting::getValue('bank_account', '—'),
            'bank_account_name' => Setting::getValue('bank_account_name', 'Amako Momo Pvt Ltd'),
        ]);
    }
}
