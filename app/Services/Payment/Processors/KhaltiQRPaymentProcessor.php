<?php

namespace App\Services\Payment\Processors;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payment\PaymentProcessorInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KhaltiQRPaymentProcessor implements PaymentProcessorInterface
{
    private $apiKey;
    private $apiEndpoint;

    public function __construct()
    {
        $this->apiKey = config('services.khalti.secret_key');
        $this->apiEndpoint = config('services.khalti.api_endpoint');
    }

    public function process(Payment $payment): bool
    {
        try {
            // Generate QR code
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->apiKey
            ])->post($this->apiEndpoint . '/qr/generate', [
                'amount' => $payment->amount * 100, // Convert to paisa
                'order_id' => $payment->id,
                'return_url' => route('payment.khalti.callback'),
                'cancel_url' => route('payment.khalti.cancel')
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to generate QR code: ' . $response->body());
            }

            $qrData = $response->json();
            
            // Update payment with QR details
            $payment->update([
                'status' => 'pending',
                'gateway_response' => json_encode([
                    'qr_id' => $qrData['qr_id'],
                    'qr_url' => $qrData['qr_url']
                ])
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Khalti QR payment processing failed', [
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
        // For QR payments, validation happens after scanning
        return true;
    }

    public function verifyPayment(string $qrId): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->apiKey
            ])->get($this->apiEndpoint . '/qr/status/' . $qrId);

            if (!$response->successful()) {
                return false;
            }

            $status = $response->json();
            return $status['status'] === 'completed';
        } catch (\Exception $e) {
            Log::error('Khalti QR payment verification failed', [
                'qr_id' => $qrId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 