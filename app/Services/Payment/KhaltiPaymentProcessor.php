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

    public function initialize(Payment $payment): PaymentResponse
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
                return PaymentResponse::redirect($data['payment_url'], 'pending', 'Khalti payment initialized', [
                    'payment_id' => $payment->id,
                    'khalti_pidx' => $data['pidx'],
                ]);
            } else {
                Log::error('Khalti payment initialization failed: ' . $response->body());
                return PaymentResponse::failure('Failed to initialize Khalti payment');
            }
        } catch (\Exception $e) {
            Log::error('Khalti payment initialization error: ' . $e->getMessage());
            return PaymentResponse::failure('Error initializing Khalti payment: ' . $e->getMessage());
        }
    }

    public function process(Payment $payment): PaymentResponse
    {
        // Khalti payments are processed on their end, we just verify the status
        return $this->verify($payment);
    }

    public function verify(Payment $payment): PaymentResponse
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
                    
                    return PaymentResponse::success('completed', 'Payment verified successfully', [
                        'payment_id' => $payment->id,
                    ]);
                }
            }
            return PaymentResponse::failure('Payment verification failed');
        } catch (\Exception $e) {
            Log::error('Khalti payment verification error: ' . $e->getMessage());
            return PaymentResponse::failure('Error verifying Khalti payment: ' . $e->getMessage());
        }
    }

    public function cancel(Payment $payment): PaymentResponse
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

                return PaymentResponse::success('cancelled', 'Payment cancelled successfully', [
                    'payment_id' => $payment->id,
                ]);
            }
            return PaymentResponse::failure('Failed to cancel payment');
        } catch (\Exception $e) {
            Log::error('Khalti payment cancellation error: ' . $e->getMessage());
            return PaymentResponse::failure('Error cancelling Khalti payment: ' . $e->getMessage());
        }
    }

    public function getRedirectResponse(Payment $payment): PaymentResponse
    {
        // In Khalti, the URL is retrieved during initialize.
        // If we need to get it again, we might need to store it or re-call initiate.
        // For now, we return failure if not stored, as Khalti doesn't have a static URL.
        return PaymentResponse::failure('Khalti redirect URL must be retrieved during initialization');
    }
} 