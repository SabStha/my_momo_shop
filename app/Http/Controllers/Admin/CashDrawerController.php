<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashDrawer;
use App\Models\CashDrawerAdjustment;
use App\Models\CashDrawerSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
                ['branch_id' => $branchId],
                ['total_cash' => 0]
            );

            // Create adjustment record
            CashDrawerAdjustment::create([
                'cash_drawer_id' => $cashDrawer->id,
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
                        '4' => 0,
                        '1' => 0
                    ],
                    'total_balance' => 0,
                    'session' => null
                ]);
            }

            // Start with opening denominations
            $denominations = $session->opening_denominations;

            // Get cash drawer
            $cashDrawer = CashDrawer::where('branch_id', $branchId)->first();
            
            if ($cashDrawer) {
                // Get all adjustments for this session
                $adjustments = CashDrawerAdjustment::where('cash_drawer_id', $cashDrawer->id)
                    ->where('created_at', '>=', $session->opened_at)
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

            // Initialize all denominations to 0 if they don't exist
            $allDenominations = [1000, 500, 100, 50, 20, 10, 4, 1];
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

            return response()->json([
                'denominations' => $denominations,
                'total_balance' => $totalBalance,
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
        $request->validate([
            'branch_id' => 'required|integer',
            'opening_balance' => 'required|numeric|min:0',
            'opening_denominations' => 'required|array',
            'notes' => 'nullable|string'
        ]);

        try {
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
                ['branch_id' => $request->branch_id],
                ['total_cash' => 0]
            );

            $cashDrawer->total_cash = $request->opening_balance;
            $cashDrawer->save();

            DB::commit();

            return response()->json([
                'message' => 'Cash drawer session opened successfully',
                'session' => $session
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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

            // Update session
            $session->closing_balance = $closingBalance;
            $session->closing_denominations = $request->closing_denominations;
            $session->closed_at = Carbon::now();
            $session->notes = $request->notes;
            $session->save();

            // Update cash drawer
            $cashDrawer = CashDrawer::where('branch_id', $request->branch_id)->first();
            if ($cashDrawer) {
                $cashDrawer->total_cash = $closingBalance;
                $cashDrawer->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Cash drawer session closed successfully',
                'session' => $session
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to close cash drawer session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDenominations(Request $request)
    {
        try {
            // Ensure user is authenticated
            if (!Auth::check()) {
                \Log::warning('Unauthenticated attempt to update denominations');
                return response()->json([
                    'message' => 'User is not logged in'
                ], 401);
            }

            // Log the authenticated user
            \Log::info('Updating denominations', [
                'user_id' => Auth::id(),
                'branch_id' => $request->branch_id
            ]);

            $request->validate([
                'branch_id' => 'required|integer',
                'denominations' => 'required|array'
            ]);

            DB::beginTransaction();

            // Get current session
            $session = CashDrawerSession::where('branch_id', $request->branch_id)
                ->whereNull('closed_at')
                ->first();

            if (!$session) {
                throw new \Exception('No open cash drawer session found');
            }

            // Get or create cash drawer
            $cashDrawer = CashDrawer::firstOrCreate(
                ['branch_id' => $request->branch_id],
                ['total_cash' => 0]
            );

            // Calculate total cash from denominations
            $totalCash = 0;
            foreach ($request->denominations as $denomination => $count) {
                $totalCash += (int)$denomination * (int)$count;
            }

            // Update cash drawer
            $cashDrawer->denominations = $request->denominations;
            $cashDrawer->total_cash = $totalCash;
            $cashDrawer->save();

            DB::commit();

            \Log::info('Denominations updated successfully', [
                'user_id' => Auth::id(),
                'branch_id' => $request->branch_id,
                'total_cash' => $totalCash
            ]);

            return response()->json([
                'message' => 'Denominations updated successfully',
                'denominations' => $request->denominations,
                'total_cash' => $totalCash
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update denominations', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'branch_id' => $request->branch_id ?? null
            ]);
            return response()->json([
                'message' => 'Failed to update denominations: ' . $e->getMessage()
            ], 500);
        }
    }
} 