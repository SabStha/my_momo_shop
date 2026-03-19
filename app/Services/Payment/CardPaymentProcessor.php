<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentProcessorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CardPaymentProcessor extends AbstractPaymentProcessor
{
    public function initialize(Payment $payment): PaymentResponse
    {
        try {
            // Log initialized
            $this->logActivity($payment, 'initialized');

            return PaymentResponse::success('pending', 'Card payment initialized', [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ]);
        } catch (\Throwable $e) {
            return PaymentResponse::failure($e->getMessage());
        }
    }

    public function process(Payment $payment): PaymentResponse
    {
        try {
            // For demo purposes, we'll simulate a successful payment
            $this->logActivity($payment, 'processing');
            
            // Simulate payment processing delay
            // sleep(2); // Disabled for faster execution in this environment

            $this->updatePaymentStatus($payment, 'completed', [
                'transaction_id' => Str::random(16),
                'processed_at' => now()->toIso8601String()
            ]);

            return PaymentResponse::success('completed', 'Payment processed successfully', [
                'payment_id' => $payment->id,
                'transaction_id' => $payment->metadata['transaction_id'] ?? null
            ]);
        } catch (\Throwable $e) {
            return PaymentResponse::failure($e->getMessage());
        }
    }

    public function verify(Payment $payment): PaymentResponse
    {
        try {
            return PaymentResponse::success($payment->status, 'Payment status verified', [
                'payment_id' => $payment->id,
                'verified_at' => now()->toIso8601String()
            ]);
        } catch (\Throwable $e) {
            return PaymentResponse::failure($e->getMessage());
        }
    }

    public function cancel(Payment $payment): PaymentResponse
    {
        try {
            if ($payment->status !== 'pending') {
                throw new \Exception('Only pending payments can be cancelled');
            }

            $this->logActivity($payment, 'cancelled');
            $this->updatePaymentStatus($payment, 'cancelled');

            return PaymentResponse::success('cancelled', 'Payment cancelled successfully', [
                'payment_id' => $payment->id,
            ]);
        } catch (\Throwable $e) {
            return PaymentResponse::failure($e->getMessage());
        }
    }

    public function getRedirectResponse(Payment $payment): PaymentResponse
    {
        return PaymentResponse::failure('Card payment does not support external redirection in this simulator');
    }

    /**
     * Detect card brand from card number
     *
     * @param string $cardNumber
     * @return string
     */
    protected function detectCardBrand(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        if (preg_match('/^4/', $cardNumber)) {
            return 'visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'amex';
        } elseif (preg_match('/^3(?:0[0-5]|[68])/', $cardNumber)) {
            return 'diners';
        } elseif (preg_match('/^6(?:011|5)/', $cardNumber)) {
            return 'discover';
        }

        return 'unknown';
    }
} 