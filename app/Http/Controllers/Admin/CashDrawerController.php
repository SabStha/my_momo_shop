<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashDrawer;
use App\Models\CashDrawerAdjustment;
use App\Models\CashDrawerSession;
use App\Services\CashDrawerAlertService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Mail\CashDrawerSessionNotification;
use Illuminate\Support\Facades\Mail;

class CashDrawerController extends Controller
{
    public function adjust(Request $request)
    {
        $request->validate([
            'denomination' => 'required|integer',
            'amount' => 'required|integer',
            'reason' => 'required|string|max:255',
            'branch_id' => 'required|integer'
        ]);

        try {
            DB::beginTransaction();

            $branchId = $request->branch_id;
            if (!$branchId) {
                throw new \Exception('Branch ID is required');
            }

            // Check if there's an open session
            $session = CashDrawerSession::where('branch_id', $branchId)
                ->whereNull('closed_at')
                ->first();

            if (!$session) {
                throw new \Exception('No open cash drawer session found');
            }

            // Get or create cash drawer for the branch
            $cashDrawer = CashDrawer::firstOrCreate(
                ['branch_id' => $branchId, 'date' => Carbon::today()],
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

            // Create adjustment record
            CashDrawerAdjustment::create([
                'cash_drawer_id' => $cashDrawer->id,
                'cash_drawer_session_id' => $session->id,
                'user_id' => Auth::id(),
                'denomination' => $request->denomination,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'type' => $request->amount > 0 ? 'add' : 'remove'
            ]);

            // Update cash drawer total
            $cashDrawer->total_cash += $request->amount;
            $cashDrawer->save();

            DB::commit();

            return response()->json([
                'message' => 'Cash drawer adjusted successfully',
                'new_balance' => $cashDrawer->total_cash
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to adjust cash drawer: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getBalance(Request $request)
    {
        try {
            $branchId = $request->branch_id;
            if (!$branchId) {
                throw new \Exception('Branch ID is required');
            }

            $cashDrawer = CashDrawer::where('branch_id', $branchId)->first();
            
            return response()->json([
                'balance' => $cashDrawer ? $cashDrawer->total_cash : 0,
                'minimum_balance' => 1000, // You can adjust these values
                'maximum_balance' => 50000 // based on your requirements
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get cash drawer balance: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatus(Request $request)
    {
        try {
            $branchId = $request->branch_id;
            if (!$branchId) {
                throw new \Exception('Branch ID is required');
            }

            // Get current session
            $session = CashDrawerSession::where('branch_id', $branchId)
                ->whereNull('closed_at')
                ->first();

            if (!$session) {
                return response()->json([
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
                    ],
                    'total_balance' => 0,
                    'session' => null
                ]);
            }

            // Get cash drawer
            $cashDrawer = CashDrawer::where('branch_id', $branchId)->first();
            
            if ($cashDrawer && $cashDrawer->denominations) {
                // Use the saved denominations from cash drawer
                $denominations = $cashDrawer->denominations;
            } else {
                // Fallback to opening denominations if no saved denominations
                $denominations = $session->opening_denominations;
                
                // Get all adjustments for this session if cash drawer exists
                if ($cashDrawer) {
                    $adjustments = CashDrawerAdjustment::where('cash_drawer_id', $cashDrawer->id)
                        ->where('cash_drawer_session_id', $session->id)
                        ->get();

                    // Apply adjustments to denominations
                    foreach ($adjustments as $adjustment) {
                        $denomination = $adjustment->denomination;
                        if (!isset($denominations[$denomination])) {
                            $denominations[$denomination] = 0;
                        }
                        $denominations[$denomination] += $adjustment->amount;
                    }
                }
            }

            // Initialize all denominations to 0 if they don't exist
            $allDenominations = [1000, 500, 100, 50, 20, 10, 5, 2, 1];
            foreach ($allDenominations as $denomination) {
                if (!isset($denominations[$denomination])) {
                    $denominations[$denomination] = 0;
                }
            }

            // Calculate total balance
            $totalBalance = 0;
            foreach ($denominations as $denomination => $count) {
                $totalBalance += $denomination * $count;
            }

            // Check for alerts
            $alertService = new CashDrawerAlertService();
            $alertSummary = $alertService->getAlertSummary($branchId, $denominations);

            return response()->json([
                'denominations' => $denominations,
                'total_balance' => $totalBalance,
                'alerts' => $alertSummary,
                'session' => [
                    'id' => $session->id,
                    'opened_at' => $session->opened_at,
                    'opened_by' => $session->user->name,
                    'opening_balance' => $session->opening_balance,
                    'opening_denominations' => $session->opening_denominations
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get cash drawer status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function openSession(Request $request)
    {
        try {
            if (!Auth::check()) {
                \Log::warning('Unauthenticated attempt to open cash drawer session');
                return response()->json([
                    'message' => 'User is not logged in'
                ], 401);
            }

            $request->validate([
                'branch_id' => 'required|integer',
                'opening_balance' => 'required|numeric|min:0',
                'opening_denominations' => 'required|array',
                'notes' => 'nullable|string'
            ]);

            DB::beginTransaction();

            // Check if there's already an open session
            $existingSession = CashDrawerSession::where('branch_id', $request->branch_id)
                ->whereNull('closed_at')
                ->first();

            if ($existingSession) {
                throw new \Exception('There is already an open cash drawer session');
            }

            // Create new session
            $session = CashDrawerSession::create([
                'branch_id' => $request->branch_id,
                'user_id' => Auth::id(),
                'opening_balance' => $request->opening_balance,
                'opening_denominations' => $request->opening_denominations,
                'opened_at' => Carbon::now(),
                'notes' => $request->notes
            ]);

            // Update cash drawer
            $cashDrawer = CashDrawer::firstOrCreate(
                ['branch_id' => $request->branch_id, 'date' => Carbon::today()],
                [
                    'date' => Carbon::today(),
                    'starting_amount' => $request->opening_balance,
                    'current_balance' => $request->opening_balance,
                    'total_cash' => $request->opening_balance,
                    'total_sales' => 0,
                    'status' => 'open',
                    'denominations' => $request->opening_denominations
                ]
            );

            $cashDrawer->total_cash = $request->opening_balance;
            $cashDrawer->save();

            DB::commit();

            $notifyEmails = array_map('trim', explode(',', config('cash_drawer.notify_emails')));
            $summary = null; // You can add an opening summary if desired
            Mail::to($notifyEmails)->send(new CashDrawerSessionNotification($session, 'opened', $summary));

            \Log::info('Cash drawer session opened successfully', [
                'user_id' => Auth::id(),
                'branch_id' => $request->branch_id,
                'session_id' => $session->id
            ]);

            return response()->json([
                'message' => 'Cash drawer session opened successfully',
                'session' => $session
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to open cash drawer session', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'branch_id' => $request->branch_id
            ]);
            return response()->json([
                'message' => 'Failed to open cash drawer session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function closeSession(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'closing_denominations' => 'required|array',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Get current session
            $session = CashDrawerSession::where('branch_id', $request->branch_id)
                ->whereNull('closed_at')
                ->first();

            if (!$session) {
                throw new \Exception('No open cash drawer session found');
            }

            // Calculate closing balance from denominations
            $closingBalance = 0;
            foreach ($request->closing_denominations as $denomination => $count) {
                $closingBalance += $denomination * $count;
            }

            // Calculate expected balance (opening + sales - payments)
            $expectedBalance = $this->calculateExpectedBalance($session);
            
            // Calculate discrepancy
            $discrepancy = $closingBalance - $expectedBalance;
            
            // Get session summary
            $sessionSummary = $this->getSessionSummary($session);

            // Update session with enhanced data
            $session->closing_balance = $closingBalance;
            $session->closing_denominations = $request->closing_denominations;
            $session->closed_at = Carbon::now();
            $session->notes = $request->notes;
            $session->discrepancy = $discrepancy;
            $session->session_duration = $session->opened_at->diffInMinutes(Carbon::now());
            $session->save();

            // Update cash drawer
            $cashDrawer = CashDrawer::where('branch_id', $request->branch_id)->first();
            if ($cashDrawer) {
                $cashDrawer->total_cash = $closingBalance;
                $cashDrawer->status = 'closed';
                $cashDrawer->denominations = $request->closing_denominations;
                $cashDrawer->save();
            }

            // Log the closing event
            \Log::info('Cash drawer session closed', [
                'session_id' => $session->id,
                'branch_id' => $request->branch_id,
                'user_id' => Auth::id(),
                'opening_balance' => $session->opening_balance,
                'closing_balance' => $closingBalance,
                'expected_balance' => $expectedBalance,
                'discrepancy' => $discrepancy,
                'session_duration_minutes' => $session->session_duration
            ]);

            DB::commit();

            $notifyEmails = array_map('trim', explode(',', config('cash_drawer.notify_emails')));
            Mail::to($notifyEmails)->send(new CashDrawerSessionNotification($session, 'closed', $sessionSummary));

            return response()->json([
                'message' => 'Cash drawer session closed successfully',
                'session' => $session,
                'summary' => $sessionSummary,
                'discrepancy' => $discrepancy,
                'expected_balance' => $expectedBalance,
                'session_duration' => $session->session_duration
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to close cash drawer session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate expected balance based on opening balance and transactions
     */
    private function calculateExpectedBalance($session)
    {
        $expectedBalance = $session->opening_balance;
        
        // Add cash sales during session
        $cashSales = \App\Models\Payment::where('branch_id', $session->branch_id)
            ->where('payment_method', 'cash')
            ->where('created_at', '>=', $session->opened_at)
            ->where('created_at', '<=', Carbon::now())
            ->sum('amount');
        
        $expectedBalance += $cashSales;
        
        // Subtract any cash refunds or adjustments
        $cashRefunds = \App\Models\Payment::where('branch_id', $session->branch_id)
            ->where('payment_method', 'cash')
            ->where('status', 'refunded')
            ->where('created_at', '>=', $session->opened_at)
            ->where('created_at', '<=', Carbon::now())
            ->sum('amount');
        
        $expectedBalance -= $cashRefunds;
        
        return $expectedBalance;
    }

    /**
     * Get session summary for reporting
     */
    private function getSessionSummary($session)
    {
        $startTime = $session->opened_at;
        $endTime = Carbon::now();
        
        // Get cash payments during session
        $cashPayments = \App\Models\Payment::where('branch_id', $session->branch_id)
            ->where('payment_method', 'cash')
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', 'completed');
        
        $totalCashSales = $cashPayments->sum('amount');
        $cashTransactionCount = $cashPayments->count();
        
        // Get all payments during session
        $allPayments = \App\Models\Payment::where('branch_id', $session->branch_id)
            ->where('created_at', '>=', $startTime)
            ->where('created_at', '<=', $endTime)
            ->where('status', 'completed');
        
        $totalSales = $allPayments->sum('amount');
        $totalTransactions = $allPayments->count();
        
        // Get payment method breakdown
        $paymentMethods = $allPayments->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');
        
        return [
            'session_duration_minutes' => $startTime->diffInMinutes($endTime),
            'opening_balance' => $session->opening_balance,
            'closing_balance' => $session->closing_balance ?? 0,
            'cash_sales' => $totalCashSales,
            'cash_transactions' => $cashTransactionCount,
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'payment_methods' => $paymentMethods,
            'opened_by' => $session->user->name ?? 'Unknown',
            'opened_at' => $startTime->format('Y-m-d H:i:s'),
            'closed_at' => $endTime->format('Y-m-d H:i:s')
        ];
    }

    public function updateDenominations(Request $request)
    {
        try {
            if (!Auth::check()) {
                \Log::warning('Unauthenticated attempt to update denominations');
                return response()->json([
                    'message' => 'User is not logged in'
                ], 401);
            }

            $request->validate([
                'branch_id' => 'required|integer',
                'denominations' => 'required|array'
            ]);

            $branchId = $request->branch_id;
            $denominations = $request->denominations;

            // Check if there's an open session
            $session = CashDrawerSession::where('branch_id', $branchId)
                ->whereNull('closed_at')
                ->first();

            if (!$session) {
                throw new \Exception('No open cash drawer session found');
            }

            // Get cash drawer
            $cashDrawer = CashDrawer::firstOrCreate(
                ['branch_id' => $branchId, 'date' => Carbon::today()],
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

            // Calculate total from denominations
            $totalBalance = 0;
            foreach ($denominations as $denomination => $count) {
                $totalBalance += $denomination * $count;
            }

            // Update cash drawer total
            $cashDrawer->total_cash = $totalBalance;
            $cashDrawer->save();

            // Create adjustment record for the update
            CashDrawerAdjustment::create([
                'cash_drawer_id' => $cashDrawer->id,
                'cash_drawer_session_id' => $session->id,
                'user_id' => Auth::id(),
                'denomination' => 0, // Special case for full update
                'amount' => 0, // Amount is calculated from denominations
                'reason' => 'Manual denomination update',
                'type' => 'update',
                'denominations' => $denominations
            ]);

            return response()->json([
                'message' => 'Denominations updated successfully',
                'total_balance' => $totalBalance,
                'denominations' => $denominations
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update denominations', [
                'error' => $e->getMessage(),
                'branch_id' => $request->branch_id,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'message' => 'Failed to update denominations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCurrentDenominations(Request $request)
    {
        try {
            $branchId = $request->branch_id;
            if (!$branchId) {
                throw new \Exception('Branch ID is required');
            }

            $cashDrawer = CashDrawer::where('branch_id', $branchId)->first();
            
            if (!$cashDrawer) {
                return response()->json([
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

            return response()->json([
                'denominations' => $cashDrawer->denominations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get current denominations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSessionSales(Request $request)
    {
        try {
            $sessionId = $request->session_id;
            if (!$sessionId) {
                throw new \Exception('Session ID is required');
            }

            // Get the session
            $session = CashDrawerSession::find($sessionId);
            if (!$session) {
                throw new \Exception('Session not found');
            }

            // Get cash payments made during this session
            $cashSales = DB::table('payments')
                ->where('payment_method', 'cash')
                ->where('branch_id', $session->branch_id)
                ->where('created_at', '>=', $session->opened_at)
                ->where('created_at', '<=', $session->closed_at ?? now())
                ->sum('amount');

            return response()->json([
                'total_cash_sales' => $cashSales,
                'session_start' => $session->opened_at,
                'session_end' => $session->closed_at,
                'branch_id' => $session->branch_id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get session sales: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Adjust cash drawer denominations with password protection
     */
    public function adjustDenominations(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'password' => 'required|string',
            'adjustments' => 'required|array',
            'reason' => 'required|string|max:255'
        ]);

        // Check password
        if ($request->password !== '333122') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 401);
        }

        try {
            DB::beginTransaction();

            $branchId = $request->branch_id;
            
            // Check if there's an open session
            $session = CashDrawerSession::where('branch_id', $branchId)
                ->whereNull('closed_at')
                ->first();

            if (!$session) {
                throw new \Exception('No open cash drawer session found');
            }

            // Get current cash drawer
            $cashDrawer = CashDrawer::where('branch_id', $branchId)->first();
            
            if (!$cashDrawer) {
                throw new \Exception('Cash drawer not found');
            }

            $totalAdjustment = 0;
            $adjustmentDetails = [];

            // Process each denomination adjustment
            foreach ($request->adjustments as $denomination => $adjustment) {
                if ($adjustment != 0) { // Only process non-zero adjustments
                    $amount = $denomination * $adjustment;
                    $totalAdjustment += $amount;
                    
                    $adjustmentDetails[] = [
                        'denomination' => $denomination,
                        'notes_adjusted' => $adjustment,
                        'amount_adjusted' => $amount
                    ];

                    // Create adjustment record
                    CashDrawerAdjustment::create([
                        'cash_drawer_id' => $cashDrawer->id,
                        'cash_drawer_session_id' => $session->id,
                        'user_id' => Auth::id(),
                        'denomination' => $denomination,
                        'amount' => $adjustment,
                        'reason' => $request->reason,
                        'type' => $adjustment > 0 ? 'add' : 'remove'
                    ]);
                }
            }

            // Update cash drawer total
            $cashDrawer->total_cash += $totalAdjustment;
            $cashDrawer->save();

            DB::commit();

            // Log the adjustment
            \Log::info('Cash drawer adjusted', [
                'user_id' => Auth::id(),
                'branch_id' => $branchId,
                'total_adjustment' => $totalAdjustment,
                'reason' => $request->reason,
                'adjustments' => $adjustmentDetails
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cash drawer adjusted successfully',
                'total_adjustment' => $totalAdjustment,
                'new_balance' => $cashDrawer->total_cash,
                'adjustments' => $adjustmentDetails
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to adjust cash drawer', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'branch_id' => $branchId
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust cash drawer: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger the physical cash drawer to open (simulated).
     */
    public function openPhysicalDrawer(Request $request)
    {
        // Here you would integrate with the hardware (e.g., via USB, serial, or network command)
        // For now, just simulate success
        // Optionally, log the action
        // \Log::info('Physical cash drawer opened by user', ['user_id' => auth()->id()]);
        return response()->json([
            'message' => 'Physical cash drawer opened successfully (simulated).'
        ], 200);
    }

    /**
     * Update denominations with password protection
     */
    public function updateDenominationsWithPassword(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'password' => 'required|string',
            'adjustments' => 'required|array',
            'reason' => 'required|string|max:255'
        ]);

        // Check password
        if ($request->password !== '333122') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password'
            ], 401);
        }

        try {
            if (!Auth::check()) {
                \Log::warning('Unauthenticated attempt to update denominations');
                return response()->json([
                    'message' => 'User is not logged in'
                ], 401);
            }

            $branchId = $request->branch_id;
            $denominations = $request->adjustments; // Use adjustments as the new denomination values

            // Check if there's an open session
            $session = CashDrawerSession::where('branch_id', $branchId)
                ->whereNull('closed_at')
                ->first();

            if (!$session) {
                throw new \Exception('No open cash drawer session found');
            }

            // Get cash drawer
            $cashDrawer = CashDrawer::firstOrCreate(
                ['branch_id' => $branchId, 'date' => Carbon::today()],
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

            // Calculate total from denominations
            $totalBalance = 0;
            foreach ($denominations as $denomination => $count) {
                $totalBalance += $denomination * $count;
            }

            // Update cash drawer with new denominations and total
            $cashDrawer->total_cash = $totalBalance;
            $cashDrawer->denominations = $denominations; // Save the actual denomination values
            $cashDrawer->save();

            // Create adjustment record for the update - use 'add' type since we're setting new values
            CashDrawerAdjustment::create([
                'cash_drawer_id' => $cashDrawer->id,
                'cash_drawer_session_id' => $session->id,
                'user_id' => Auth::id(),
                'denomination' => 0, // Special case for full update
                'amount' => 0, // Amount is calculated from denominations
                'reason' => $request->reason,
                'type' => 'add' // Use 'add' instead of 'update'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Denominations updated successfully',
                'total_balance' => $totalBalance,
                'denominations' => $denominations
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update denominations', [
                'error' => $e->getMessage(),
                'branch_id' => $request->branch_id,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update denominations: ' . $e->getMessage()
            ], 500);
        }
    }
} 