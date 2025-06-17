<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Payment\PaymentReceiptGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    private $receiptGenerator;

    public function __construct(PaymentReceiptGenerator $receiptGenerator)
    {
        $this->receiptGenerator = $receiptGenerator;
    }

    public function handleKhaltiWebhook(Request $request)
    {
        try {
            $payload = $request->all();
            Log::info('Khalti webhook received', ['payload' => $payload]);

            // Verify webhook signature
            if (!$this->verifyKhaltiSignature($request)) {
                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $payment = Payment::where('gateway_response->qr_id', $payload['qr_id'])->first();
            
            if (!$payment) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            if ($payload['status'] === 'completed') {
                $payment->update([
                    'status' => 'completed',
                    'processed_at' => now(),
                    'gateway_response' => json_encode(array_merge(
                        json_decode($payment->gateway_response, true),
                        ['payment_confirmation' => $payload]
                    ))
                ]);

                // Generate and send receipt
                $receiptPath = $this->receiptGenerator->generateReceipt($payment);
                $this->receiptGenerator->sendReceiptEmail($payment, $receiptPath);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'gateway_response' => json_encode(array_merge(
                        json_decode($payment->gateway_response, true),
                        ['failure_reason' => $payload['reason'] ?? 'Unknown']
                    ))
                ]);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Khalti webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all()
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    private function verifyKhaltiSignature(Request $request): bool
    {
        $signature = $request->header('X-Khalti-Signature');
        $payload = $request->getContent();
        $secret = config('services.khalti.webhook_secret');

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Handle payment gateway webhook (e.g., Khalti)
     */
    public function paymentStatus(Request $request)
    {
        Log::info('Webhook received', $request->all());

        // Example: Khalti sends payment_id and status
        $paymentId = $request->input('payment_id');
        $status = $request->input('status');

        $payment = Payment::find($paymentId);
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        }

        // Update payment status
        $payment->status = $status;
        $payment->save();

        return response()->json(['success' => true]);
    }
} 