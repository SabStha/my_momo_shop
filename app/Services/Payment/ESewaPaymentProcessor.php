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

    public function initialize(Payment $payment): array
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

            return [
                'success' => true,
                'message' => 'eSewa payment initialized successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'transaction_id' => $transactionId,
                    'payment_url' => $paymentUrl,
                    'redirect_required' => true,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('eSewa payment initialization failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to initialize eSewa payment: ' . $e->getMessage(),
            ];
        }
    }

    public function process(Payment $payment): array
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

            return [
                'success' => true,
                'message' => 'Payment redirected to eSewa',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => 'pending',
                    'redirect_required' => true,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('eSewa payment processing failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process eSewa payment: ' . $e->getMessage(),
            ];
        }
    }

    public function verify(Payment $payment): array
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

                    return [
                        'success' => true,
                        'message' => 'Payment verified successfully',
                        'data' => [
                            'payment_id' => $payment->id,
                            'status' => 'completed',
                            'transaction_id' => $transactionId,
                        ],
                    ];
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

                    return [
                        'success' => false,
                        'message' => 'Payment verification failed',
                        'data' => [
                            'payment_id' => $payment->id,
                            'status' => 'failed',
                            'transaction_id' => $transactionId,
                        ],
                    ];
                }
            } else {
                throw new \Exception('Failed to connect to eSewa verification service');
            }
        } catch (\Exception $e) {
            Log::error('eSewa payment verification failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to verify eSewa payment: ' . $e->getMessage(),
            ];
        }
    }

    public function cancel(Payment $payment): array
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

            return [
                'success' => true,
                'message' => 'Payment cancelled successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'status' => 'cancelled',
                ],
            ];
        } catch (\Exception $e) {
            Log::error('eSewa payment cancellation failed: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to cancel eSewa payment: ' . $e->getMessage(),
            ];
        }
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
