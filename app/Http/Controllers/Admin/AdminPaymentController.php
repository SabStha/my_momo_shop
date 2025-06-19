<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CashDrawer;
use App\Models\Table;
use App\Models\CashDrawerSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Wallet;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch', 1);

        // Set branch ID in session
        session(['selected_branch_id' => $branchId]);

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
                'payment_method' => 'required|in:cash,card,wallet',
                'amount_received' => 'required_if:payment_method,cash|numeric|min:0',
                'change_amount' => 'required_if:payment_method,cash|numeric|min:0',
                'branch_id' => 'required|exists:branches,id'
            ]);

            // For cash payments, check if there's an active cash drawer session
            if ($request->payment_method === 'cash') {
                $session = CashDrawerSession::where('branch_id', $request->branch_id)
                    ->whereNull('closed_at')
                    ->first();

                if (!$session) {
                    throw new \Exception('Please open a cash drawer session before processing cash payments.');
                }

                // Validate amount received
                if ($request->amount_received < $request->amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Amount received cannot be less than the total amount.'
                    ], 400);
                }
            }

            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'amount' => $request->amount,
                'currency' => 'INR',
                'status' => 'completed',
                'transaction_id' => $request->reference_number,
                'branch_id' => $order->branch_id,
                'payment_details' => [
                    'payment_method' => $request->payment_method,
                    'amount_received' => $request->amount_received,
                    'change_amount' => $request->change_amount,
                    'processed_by' => auth()->id(),
                    'branch_id' => $order->branch_id
                ],
                'completed_at' => now()
            ]);

            // Update order status
            $order->status = 'completed';
            $order->payment_status = 'paid';
            $order->save();

            // If payment is cash, update cash drawer
            if ($request->payment_method === 'cash') {
                $cashDrawer = CashDrawer::firstOrCreate(
                    ['branch_id' => $request->branch_id, 'date' => Carbon::today()],
                    [
                        'date' => Carbon::today(),
                        'starting_amount' => 0,
                        'current_balance' => 0,
                        'total_cash' => 0,
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
                    ]
                );

                $cashDrawer->total_cash += $request->amount;
                $cashDrawer->total_sales += $request->amount;
                $cashDrawer->save();
            }
            // If payment is wallet, update user's wallet balance
            elseif ($request->payment_method === 'wallet') {
                if (!$order->user_id) {
                    throw new \Exception('Wallet payment requires a registered user.');
                }
                
                $user = $order->user;
                $wallet = $user->wallet;
                
                if (!$wallet) {
                    throw new \Exception('User does not have a wallet.');
                }
                
                if ($wallet->balance < $request->amount) {
                    throw new \Exception('Insufficient wallet balance.');
                }
                
                $wallet->addBalance($request->amount, 'debit');
            }

            // Update table status if it's a dine-in order
            if ($order->table_id) {
                $table = Table::where('id', $order->table_id)
                    ->where('branch_id', $order->branch_id)
                    ->first();

                if ($table) {
                    $table->update([
                        'status' => 'available',
                        'is_occupied' => false
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
        $branchId = $request->query('branch', session('selected_branch_id'));
        
        $cashDrawer = CashDrawer::where('branch_id', $branchId)
            ->whereDate('date', Carbon::today())
            ->where('status', 'open')
            ->first();

        return response()->json([
            'is_open' => $cashDrawer ? true : false,
            'total_cash' => $cashDrawer ? $cashDrawer->current_balance : 0
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
                'status' => 'completed'
            ]);

            // Update order status
            $order->update([
                'status' => 'paid',
                'payment_status' => 'paid'
            ]);

            // If cash payment, handle cash drawer
            if ($request->payment_method === 'cash') {
                $cashDrawer = CashDrawer::where('branch_id', $branchId)
                    ->where('status', 'open')
                    ->first();

                if ($cashDrawer) {
                    $cashDrawer->increment('total_cash', $request->amount);
                    $cashDrawer->increment('total_sales', $request->amount);
                }
            }

            // If wallet payment, handle wallet balance
            if ($request->payment_method === 'wallet' && $request->wallet_number) {
                $wallet = Wallet::where('wallet_number', $request->wallet_number)
                    ->where('branch_id', $branchId)
                    ->first();

                if ($wallet) {
                    $wallet->decrement('balance', $request->amount);
                }
            }

            return response()->json([
                'message' => 'Payment processed successfully',
                'payment' => $payment
            ]);

        } catch (\Exception $e) {
            \Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json(['message' => 'Payment processing failed: ' . $e->getMessage()], 500);
        }
    }
} 