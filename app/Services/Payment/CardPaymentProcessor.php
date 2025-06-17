<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentProcessorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CardPaymentProcessor extends AbstractPaymentProcessor
{
    public function initialize(array $data): array
    {
        try {
            $payment = Payment::create([
                'user_id' => $data['user_id'],
                'order_id' => $data['order_id'],
                'payment_method_id' => $this->method->id,
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'USD',
                'status' => 'pending',
                'metadata' => [
                    'card_last4' => substr($data['card_number'], -4),
                    'card_brand' => $this->detectCardBrand($data['card_number']),
                ]
            ]);

            $this->logActivity($payment, 'initialized');

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function process(array $data): array
    {
        try {
            $payment = Payment::findOrFail($data['payment_id']);

            // In a real implementation, you would integrate with a payment gateway here
            // For example, using Stripe:
            // $response = Http::withHeaders([
            //     'Authorization' => 'Bearer ' . $this->config['secret_key']
            // ])->post('https://api.stripe.com/v1/charges', [
            //     'amount' => $payment->amount * 100, // Convert to cents
            //     'currency' => strtolower($payment->currency),
            //     'source' => $data['token'],
            //     'description' => "Payment for Order #{$payment->order_id}"
            // ]);

            // For demo purposes, we'll simulate a successful payment
            $this->logActivity($payment, 'processing');
            
            // Simulate payment processing delay
            sleep(2);

            $this->updatePaymentStatus($payment, 'completed', [
                'transaction_id' => Str::random(16),
                'processed_at' => now()->toIso8601String()
            ]);

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'transaction_id' => $payment->metadata['transaction_id']
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function verify(string $paymentId): array
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            // In a real implementation, you would verify the payment with the payment gateway
            // For example, using Stripe:
            // $response = Http::withHeaders([
            //     'Authorization' => 'Bearer ' . $this->config['secret_key']
            // ])->get("https://api.stripe.com/v1/charges/{$payment->metadata['transaction_id']}");

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status,
                'verified_at' => now()->toIso8601String()
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function cancel(string $paymentId): array
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            if ($payment->status !== 'pending') {
                throw new \Exception('Only pending payments can be cancelled');
            }

            $this->logActivity($payment, 'cancelled');
            $this->updatePaymentStatus($payment, 'cancelled');

            return [
                'success' => true,
                'payment_id' => $payment->id,
                'status' => $payment->status
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
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