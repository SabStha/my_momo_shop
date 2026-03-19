<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentProcessorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ESewaPaymentProcessor implements PaymentProcessorInterface
{
    protected array $config;

    public function __construct()
    {
        // eSewa configuration - these would typically come from environment variables
        $this->config = [
            'merchant_id' => config('services.esewa.merchant_id', 'EPAYTEST'),
            'merchant_secret' => config('services.esewa.merchant_secret', ''),
            'test_mode' => config('services.esewa.test_mode', true),
            'base_url' => config('services.esewa.test_mode', true) 
                ? 'https://esewa.com.np/epay/testtransac' 
                : 'https://esewa.com.np/epay/main',
            'verification_url' => config('services.esewa.test_mode', true)
                ? 'https://esewa.com.np/epay/testtransac/valid'
                : 'https://esewa.com.np/epay/transrec',
        ];
    }

    public function initialize(Payment $payment): PaymentResponse
    {
        try {
            // Generate unique transaction ID
            $transactionId = 'ESEWA_' . uniqid() . '_' . time();
            
            // Store transaction ID in payment metadata
            $payment->metadata = array_merge($payment->metadata ?? [], [
                'transaction_id' => $transactionId,
                'esewa_merchant_id' => $this->config['merchant_id'],
                'initialized_at' => now()->toISOString(),
            ]);
            $payment->save();

            // Generate eSewa payment URL
            $paymentUrl = $this->generatePaymentUrl($payment, $transactionId);

            return PaymentResponse::redirect($paymentUrl, 'pending', 'eSewa payment initialized successfully', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
            ]);
        } catch (\Exception $e) {
            Log::error('eSewa payment initialization failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return PaymentResponse::failure('Failed to initialize eSewa payment: ' . $e->getMessage());
        }
    }

    public function process(Payment $payment): PaymentResponse
    {
        try {
            // For eSewa, the actual payment happens on their platform
            // We just need to update the payment status to pending
            $payment->update([
                'status' => 'pending',
                'metadata' => array_merge($payment->metadata ?? [], [
                    'processed_at' => now()->toISOString(),
                    'status' => 'redirected_to_esewa',
                ]),
            ]);

            return PaymentResponse::success('pending', 'Payment redirected to eSewa', [
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('eSewa payment processing failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return PaymentResponse::failure('Failed to process eSewa payment: ' . $e->getMessage());
        }
    }

    public function verify(Payment $payment): PaymentResponse
    {
        try {
            $metadata = $payment->metadata ?? [];
            $transactionId = $metadata['transaction_id'] ?? null;

            if (!$transactionId) {
                throw new \Exception('Transaction ID not found in payment metadata');
            }

            // Verify payment with eSewa
            $verificationData = [
                'amt' => $payment->amount,
                'rid' => $transactionId,
                'pid' => $payment->id,
                'scd' => $this->config['merchant_secret'],
            ];

            $response = Http::post($this->config['verification_url'], $verificationData);

            if ($response->successful()) {
                $responseText = $response->body();
                
                // eSewa returns "Success" or "Failure" in response body
                if (str_contains($responseText, 'Success')) {
                    // Payment verified successfully
                    $payment->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'metadata' => array_merge($metadata, [
                            'verified_at' => now()->toISOString(),
                            'esewa_response' => $responseText,
                            'verification_status' => 'success',
                        ]),
                    ]);

                    return PaymentResponse::success('completed', 'Payment verified successfully', [
                        'payment_id' => $payment->id,
                        'transaction_id' => $transactionId,
                    ]);
                } else {
                    // Payment verification failed
                    $payment->update([
                        'status' => 'failed',
                        'failed_at' => now(),
                        'metadata' => array_merge($metadata, [
                            'verified_at' => now()->toISOString(),
                            'esewa_response' => $responseText,
                            'verification_status' => 'failed',
                        ]),
                    ]);

                    return PaymentResponse::failure('Payment verification failed', 'failed', [
                        'payment_id' => $payment->id,
                        'transaction_id' => $transactionId,
                    ]);
                }
            } else {
                throw new \Exception('Failed to connect to eSewa verification service');
            }
        } catch (\Exception $e) {
            Log::error('eSewa payment verification failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return PaymentResponse::failure('Failed to verify eSewa payment: ' . $e->getMessage());
        }
    }

    public function cancel(Payment $payment): PaymentResponse
    {
        try {
            $payment->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'metadata' => array_merge($payment->metadata ?? [], [
                    'cancelled_at' => now()->toISOString(),
                    'cancellation_reason' => 'User cancelled payment',
                ]),
            ]);

            return PaymentResponse::success('cancelled', 'Payment cancelled successfully', [
                'payment_id' => $payment->id,
            ]);
        } catch (\Exception $e) {
            Log::error('eSewa payment cancellation failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return PaymentResponse::failure('Failed to cancel eSewa payment: ' . $e->getMessage());
        }
    }

    public function getRedirectResponse(Payment $payment): PaymentResponse
    {
        $metadata = $payment->metadata ?? [];
        $transactionId = $metadata['transaction_id'] ?? null;
        
        if (!$transactionId) {
            $transactionId = 'ESEWA_' . uniqid() . '_' . time();
            $payment->metadata = array_merge($metadata, ['transaction_id' => $transactionId]);
            $payment->save();
        }

        $paymentUrl = $this->generatePaymentUrl($payment, $transactionId);
        
        return PaymentResponse::redirect($paymentUrl, 'pending', 'Redirecting to eSewa payment gateway');
    }

    protected function generatePaymentUrl(Payment $payment, string $transactionId): string
    {
        $params = [
            'amt' => $payment->amount,
            'pdc' => 0, // Delivery charge
            'psc' => 0, // Service charge
            'txAmt' => 0, // Tax amount
            'tAmt' => $payment->amount, // Total amount
            'pid' => $payment->id, // Product ID (we use payment ID)
            'scd' => $this->config['merchant_id'], // Merchant code
            'su' => route('payment.esewa.success'), // Success URL
            'fu' => route('payment.esewa.failure'), // Failure URL
        ];

        $queryString = http_build_query($params);
        return $this->config['base_url'] . '?' . $queryString;
    }
}
