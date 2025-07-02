<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CreditsAccount;
use App\Models\CreditsTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditsTopUpController extends Controller
{
    public function scanner()
    {
        return view('employee.credits.scanner');
    }

    public function processQR(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string'
        ]);

        try {
            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData || !isset($qrData['credits_barcode'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code data.'
                ]);
            }

            $creditsAccount = CreditsAccount::where('credits_barcode', $qrData['credits_barcode'])
                ->with('user')
                ->first();

            if (!$creditsAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credits account not found.'
                ]);
            }

            return response()->json([
                'success' => true,
                'wallet' => [
                    'id' => $creditsAccount->id,
                    'user_name' => $creditsAccount->user->name,
                    'account_number' => $creditsAccount->account_number,
                    'credits_barcode' => $creditsAccount->credits_barcode,
                    'current_balance' => $creditsAccount->credits_balance
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing QR code: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing QR code.'
            ]);
        }
    }

    public function balanceByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        try {
            $creditsAccount = CreditsAccount::where('credits_barcode', $request->barcode)
                ->with('user')
                ->first();

            if (!$creditsAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credits account not found.'
                ]);
            }

            return response()->json([
                'success' => true,
                'wallet' => [
                    'id' => $creditsAccount->id,
                    'user_name' => $creditsAccount->user->name,
                    'account_number' => $creditsAccount->account_number,
                    'credits_barcode' => $creditsAccount->credits_barcode,
                    'current_balance' => $creditsAccount->credits_balance
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error looking up barcode: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error looking up barcode.'
            ]);
        }
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'credits_account_id' => 'required|exists:credits_accounts,id',
            'credits_amount' => 'required|integer|min:1|max:1000',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $creditsAccount = CreditsAccount::findOrFail($request->credits_account_id);
            $balanceBefore = $creditsAccount->credits_balance;
            
            // Add credits
            $creditsAccount->addCredits($request->credits_amount, 'credit');
            
            $balanceAfter = $creditsAccount->credits_balance;

            // Create transaction record
            $transaction = CreditsTransaction::create([
                'credits_account_id' => $creditsAccount->id,
                'user_id' => $creditsAccount->user_id,
                'branch_id' => session('branch_id'),
                'credits_amount' => $request->credits_amount,
                'type' => 'credit',
                'description' => $request->description ?? 'Credits top-up',
                'status' => 'completed',
                'performed_by' => Auth::id(),
                'performed_by_branch_id' => session('branch_id'),
                'credits_balance_before' => $balanceBefore,
                'credits_balance_after' => $balanceAfter
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Credits top-up successful!',
                'transaction' => [
                    'id' => $transaction->id,
                    'credits_amount' => $transaction->credits_amount,
                    'new_balance' => $balanceAfter,
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at->format('M d, Y H:i')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing credits top-up: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing credits top-up.'
            ]);
        }
    }
}
