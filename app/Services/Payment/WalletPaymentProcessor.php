<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletPaymentProcessor implements PaymentProcessorInterface
{
    public function initialize(Payment $payment): array
    {
        // For wallet payments, we can directly process without external API calls
        return [
            'success' => true,
            'message' => 'Wallet payment initialized',
            'data' => [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ],
        ];
    }

    public function process(Payment $payment): array
    {
        try {
            DB::beginTransaction();

            $wallet = Wallet::where('user_id', $payment->user_id)->first();
            if (!$wallet) {
                throw new \Exception('Wallet not found for user');
            }

            if ($wallet->credits_balance < $payment->amount) {
                throw new \Exception('Insufficient wallet balance');
            }

            // Deduct amount from wallet
            $wallet->credits_balance -= $payment->amount;
            $wallet->save();

            // Create wallet transaction record
            WalletTransaction::create([
                'credits_account_id' => $wallet->id,
                'user_id' => $payment->user_id,
                'credits_amount' => $payment->amount,
                'type' => 'debit',
                'description' => 'Payment for order #' . $payment->order_id,
                'status' => 'completed',
                'credits_balance_before' => $wallet->credits_balance + $payment->amount,
                'credits_balance_after' => $wallet->credits_balance
            ]);

            // Update payment status
            $payment->status = 'completed';
            $payment->completed_at = now();
            $payment->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet payment processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ];
        }
    }

    public function verify(Payment $payment): array
    {
        // For wallet payments, verification is straightforward
        return [
            'success' => $payment->status === 'completed',
            'message' => $payment->status === 'completed' ? 'Payment verified' : 'Payment not completed',
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ],
        ];
    }

    public function cancel(Payment $payment): array
    {
        try {
            DB::beginTransaction();

            if ($payment->status === 'completed') {
                // Refund the amount back to the wallet
                $wallet = Wallet::where('user_id', $payment->user_id)->first();
                if ($wallet) {
                    $wallet->balance += $payment->amount;
                    $wallet->save();

                    // Create refund transaction record
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'amount' => $payment->amount,
                        'type' => 'refund',
                        'reference' => 'refund_' . $payment->id,
                        'description' => 'Refund for payment #' . $payment->id,
                    ]);
                }
            }

            $payment->status = 'cancelled';
            $payment->cancelled_at = now();
            $payment->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Payment cancelled successfully',
                'data' => [
                    'payment_id' => $payment->id,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet payment cancellation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment cancellation failed: ' . $e->getMessage(),
            ];
        }
    }
} 