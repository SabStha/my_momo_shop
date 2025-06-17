<?php

namespace App\Services\Payment\Processors;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payment\PaymentProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletPaymentProcessor implements PaymentProcessorInterface
{
    public function process(Payment $payment): bool
    {
        try {
            DB::beginTransaction();

            $wallet = $payment->user->wallet;
            
            if (!$wallet || $wallet->balance < $payment->amount) {
                throw new \Exception('Insufficient wallet balance');
            }

            // Deduct from wallet
            $wallet->decrement('balance', $payment->amount);

            // Update payment status
            $payment->update([
                'status' => 'completed',
                'processed_at' => now(),
                'gateway_response' => json_encode([
                    'transaction_id' => 'WALLET_' . uniqid(),
                    'wallet_balance' => $wallet->balance
                ])
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Wallet payment processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            $payment->update([
                'status' => 'failed',
                'gateway_response' => json_encode([
                    'error' => $e->getMessage()
                ])
            ]);

            return false;
        }
    }

    public function validate(Payment $payment): bool
    {
        $wallet = $payment->user->wallet;
        return $wallet && $wallet->balance >= $payment->amount;
    }
} 