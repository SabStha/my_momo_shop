<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KhaltiPaymentProcessor implements PaymentProcessorInterface
{
    protected $baseUrl;
    protected $merchantId;
    protected $secretKey;

    public function __construct()
    {
        $this->baseUrl = env('KHALTI_API_URL', 'https://khalti.com/api/v2');
        $this->merchantId = env('KHALTI_MERCHANT_ID');
        $this->secretKey = env('KHALTI_SECRET_KEY');
    }

    public function initialize(Payment $payment): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->secretKey,
            ])->post($this->baseUrl . '/epayment/initiate/', [
                'merchant_id' => $this->merchantId,
                'amount' => $payment->amount * 100, // Khalti expects amount in paisa
                'currency' => $payment->currency,
                'return_url' => route('payments.verify', $payment->id),
                'cancel_url' => route('payments.cancel', $payment->id),
                'reference' => 'payment_' . $payment->id,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message' => 'Khalti payment initialized',
                    'data' => [
                        'payment_id' => $payment->id,
                        'khalti_payment_url' => $data['payment_url'],
                        'khalti_pidx' => $data['pidx'],
                    ],
                ];
            } else {
                Log::error('Khalti payment initialization failed: ' . $response->body());
                return [
                    'success' => false,
                    'message' => 'Failed to initialize Khalti payment',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Khalti payment initialization error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error initializing Khalti payment: ' . $e->getMessage(),
            ];
        }
    }

    public function process(Payment $payment): array
    {
        // Khalti payments are processed on their end, we just verify the status
        return $this->verify($payment);
    }

    public function verify(Payment $payment): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->secretKey,
            ])->get($this->baseUrl . '/epayment/lookup/', [
                'pidx' => $payment->payment_details['khalti_pidx'] ?? null,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if ($data['status'] === 'Completed') {
                    $payment->status = 'completed';
                    $payment->completed_at = now();
                    $payment->save();
                    return [
                        'success' => true,
                        'message' => 'Payment verified successfully',
                        'data' => [
                            'payment_id' => $payment->id,
                            'status' => 'completed',
                        ],
                    ];
                }
            }
            return [
                'success' => false,
                'message' => 'Payment verification failed',
            ];
        } catch (\Exception $e) {
            Log::error('Khalti payment verification error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error verifying Khalti payment: ' . $e->getMessage(),
            ];
        }
    }

    public function cancel(Payment $payment): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->secretKey,
            ])->post($this->baseUrl . '/epayment/cancel/', [
                'pidx' => $payment->payment_details['khalti_pidx'] ?? null,
            ]);

            if ($response->successful()) {
                $payment->status = 'cancelled';
                $payment->cancelled_at = now();
                $payment->save();
                return [
                    'success' => true,
                    'message' => 'Payment cancelled successfully',
                    'data' => [
                        'payment_id' => $payment->id,
                    ],
                ];
            }
            return [
                'success' => false,
                'message' => 'Failed to cancel payment',
            ];
        } catch (\Exception $e) {
            Log::error('Khalti payment cancellation error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error cancelling Khalti payment: ' . $e->getMessage(),
            ];
        }
    }
} 