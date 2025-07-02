<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WalletTopUpController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:employee|admin']);
    }

    /**
     * Show the QR scanner interface
     */
    public function scanner()
    {
        return view('employee.wallet.scanner');
    }

    /**
     * Process scanned QR code and show top-up form
     */
    public function processQR(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string'
        ]);

        try {
            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData || !isset($qrData['wallet_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code data'
                ], 400);
            }

            $wallet = Wallet::with('user')->find($qrData['wallet_id']);
            
            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'wallet' => [
                    'id' => $wallet->id,
                    'wallet_number' => $wallet->wallet_number,
                    'barcode' => $wallet->barcode,
                    'current_balance' => $wallet->balance,
                    'user_name' => $wallet->user->name,
                    'user_email' => $wallet->user->email
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('QR Processing Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing QR code'
            ], 500);
        }
    }

    /**
     * Process wallet top-up
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01|max:10000',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $wallet = Wallet::with('user')->findOrFail($request->wallet_id);
            $employee = Auth::user();

            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $request->amount;

            // Create transaction record
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $wallet->user_id,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => $request->description ?? 'Wallet top-up by employee',
                'status' => 'completed',
                'reference_number' => 'TOPUP-' . uniqid(),
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'performed_by' => $employee->id
            ]);

            // Update wallet balance
            $wallet->addBalance($request->amount, 'credit');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wallet topped up successfully',
                'transaction' => [
                    'id' => $transaction->id,
                    'amount' => $transaction->amount,
                    'new_balance' => $wallet->balance,
                    'reference_number' => $transaction->reference_number
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet Top-up Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process top-up'
            ], 500);
        }
    }

    /**
     * Get wallet balance by barcode
     */
    public function getBalanceByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        try {
            $wallet = Wallet::with('user')->where('barcode', $request->barcode)->first();
            
            if (!$wallet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wallet not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'wallet' => [
                    'id' => $wallet->id,
                    'wallet_number' => $wallet->wallet_number,
                    'barcode' => $wallet->barcode,
                    'balance' => $wallet->balance,
                    'user_name' => $wallet->user->name,
                    'user_email' => $wallet->user->email
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Barcode Lookup Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error looking up wallet'
            ], 500);
        }
    }
}
