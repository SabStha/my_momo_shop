<?php

namespace App\Services\Payment\Processors;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payment\Contracts\PaymentProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletPaymentProcessor implements PaymentProcessorInterface
{
    public function process(array $data): array
    {
        try {
            DB::beginTransaction();

            $payment = Payment::find($data['payment_id']);
            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            $wallet = $payment->user->wallet;
            
            if (!$wallet || $wallet->credits_balance < $payment->amount) {
                throw new \Exception('Insufficient wallet balance');
            }

            // Deduct from wallet
            $wallet->decrement('credits_balance', $payment->amount);

            // Update payment status
            $payment->update([
                'status' => 'completed',
                'processed_at' => now(),
                'gateway_response' => json_encode([
                    'transaction_id' => 'WALLET_' . uniqid(),
                    'wallet_balance' => $wallet->credits_balance
                ])
            ]);

            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'wallet_balance' => $wallet->credits_balance
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Wallet payment processing failed', [
                'payment_id' => $data['payment_id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            if (isset($payment)) {
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => json_encode([
                        'error' => $e->getMessage()
                    ])
                ]);
            }

            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }

    public function initialize(array $data): array
    {
        return [
            'success' => true,
            'message' => 'Wallet payment initialized',
            'data' => $data
        ];
    }

    public function verify(string $paymentId): array
    {
        $payment = Payment::find($paymentId);
        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Payment not found'
            ];
        }

        return [
            'success' => $payment->status === 'completed',
            'message' => $payment->status === 'completed' ? 'Payment verified' : 'Payment not completed',
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status
            ]
        ];
    }

    public function cancel(string $paymentId): array
    {
        try {
            DB::beginTransaction();

            $payment = Payment::find($paymentId);
            if (!$payment) {
                throw new \Exception('Payment not found');
            }

            if ($payment->status === 'completed') {
                // Refund the amount back to the wallet
                $wallet = $payment->user->wallet;
                if ($wallet) {
                    $wallet->increment('credits_balance', $payment->amount);
                }
            }

            $payment->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Payment cancelled successfully',
                'data' => [
                    'payment_id' => $payment->id
                ]
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Wallet payment cancellation failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Payment cancellation failed: ' . $e->getMessage()
            ];
        }
    }
} 