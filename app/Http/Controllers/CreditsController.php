<?php

namespace App\Http\Controllers;

use App\Models\CreditsAccount;
use App\Models\CreditsTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $creditsAccount = $user->creditsAccount;

        if (!$creditsAccount) {
            // Create credits account if it doesn't exist
            $creditsAccount = CreditsAccount::create([
                'user_id' => $user->id,
                'credits_balance' => 0,
                'total_credits_earned' => 0,
                'total_credits_spent' => 0,
                'is_active' => true
            ]);
        }

        $transactions = $creditsAccount->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.credits.index', compact('creditsAccount', 'transactions'));
    }

    public function transactions()
    {
        $user = Auth::user();
        $creditsAccount = $user->creditsAccount;

        if (!$creditsAccount) {
            return redirect()->route('user.credits.index')
                ->with('error', 'Credits account not found.');
        }

        $transactions = $creditsAccount->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('user.credits.transactions', compact('creditsAccount', 'transactions'));
    }

    public function generateQR()
    {
        $user = Auth::user();
        $creditsAccount = $user->creditsAccount;

        if (!$creditsAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Credits account not found.'
            ]);
        }

        try {
            $qrCode = $creditsAccount->generateQRCode();
            
            // Check if it's a URL (fallback) or binary data (local generation)
            if (filter_var($qrCode, FILTER_VALIDATE_URL)) {
                // It's a URL from online service
                return response()->json([
                    'success' => true,
                    'qr_code' => $qrCode,
                    'qr_type' => 'url',
                    'account_number' => $creditsAccount->account_number,
                    'credits_barcode' => $creditsAccount->credits_barcode
                ]);
            } else {
                // It's binary data from local generation
                return response()->json([
                    'success' => true,
                    'qr_code' => 'data:image/png;base64,' . base64_encode($qrCode),
                    'qr_type' => 'data',
                    'account_number' => $creditsAccount->account_number,
                    'credits_barcode' => $creditsAccount->credits_barcode
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error generating QR code: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating QR code.'
            ]);
        }
    }

    public function getBalance()
    {
        $user = Auth::user();
        $creditsAccount = $user->creditsAccount;

        if (!$creditsAccount) {
            return response()->json([
                'success' => false,
                'message' => 'Credits account not found.'
            ]);
        }

        return response()->json([
            'success' => true,
            'credits_balance' => $creditsAccount->credits_balance,
            'display_credits' => $creditsAccount->display_credits,
            'account_number' => $creditsAccount->account_number
        ]);
    }

    /**
     * Add credits to user account (for admin/employee use)
     */
    public function addCredits(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'credits_amount' => 'required|integer|min:1|max:10000',
            'description' => 'nullable|string|max:255',
            'type' => 'required|in:credit,debit'
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->user_id);
            $creditsAccount = $user->creditsAccount;

            if (!$creditsAccount) {
                // Create credits account if it doesn't exist
                $creditsAccount = CreditsAccount::create([
                    'user_id' => $user->id,
                    'credits_balance' => 0,
                    'total_credits_earned' => 0,
                    'total_credits_spent' => 0,
                    'is_active' => true
                ]);
            }

            $balanceBefore = $creditsAccount->credits_balance;
            
            // Add or subtract credits
            $creditsAccount->addCredits($request->credits_amount, $request->type);
            
            $balanceAfter = $creditsAccount->credits_balance;

            // Create transaction record
            $transaction = CreditsTransaction::create([
                'credits_account_id' => $creditsAccount->id,
                'user_id' => $user->id,
                'branch_id' => session('branch_id'),
                'credits_amount' => $request->credits_amount,
                'type' => $request->type,
                'description' => $request->description ?? 'Manual adjustment',
                'status' => 'completed',
                'performed_by' => Auth::id(),
                'performed_by_branch_id' => session('branch_id'),
                'credits_balance_before' => $balanceBefore,
                'credits_balance_after' => $balanceAfter
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Credits updated successfully.',
                'transaction' => $transaction,
                'new_balance' => $creditsAccount->credits_balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding credits: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating credits.'
            ]);
        }
    }
}
