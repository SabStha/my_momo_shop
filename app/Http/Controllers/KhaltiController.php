<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KhaltiController extends Controller
{
    private $secretKey;
    private $publicKey;
    private $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('services.khalti.secret_key');
        $this->publicKey = config('services.khalti.public_key');
        $this->baseUrl = config('services.khalti.base_url');
    }

    public function initiatePayment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'amount' => 'required|numeric|min:1'
            ]);

            $order = Order::findOrFail($request->order_id);
            
            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'amount' => $request->amount,
                'payment_method' => 'khalti',
                'status' => 'pending'
            ]);

            // Prepare data for Khalti API
            $data = [
                'return_url' => route('khalti.return'),
                'website_url' => config('app.url'),
                'amount' => $request->amount * 100, // Convert to paisa
                'purchase_order_id' => $order->order_number,
                'purchase_order_name' => "Order #{$order->order_number}",
                'customer_info' => [
                    'name' => $order->customer_name ?? 'Customer',
                    'email' => $order->customer_email ?? 'customer@example.com',
                    'phone' => $order->customer_phone ?? '9800000000'
                ]
            ];

            // Make request to Khalti API
            $response = Http::withHeaders([
                'Authorization' => "Key {$this->secretKey}"
            ])->post("{$this->baseUrl}/epayment/initiate/", $data);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Update payment record with Khalti payment ID
                $payment->update([
                    'payment_details' => [
                        'khalti_payment_id' => $responseData['pidx'],
                        'payment_url' => $responseData['payment_url']
                    ]
                ]);

                return response()->json([
                    'success' => true,
                    'payment_url' => $responseData['payment_url'],
                    'pidx' => $responseData['pidx']
                ]);
            }

            throw new \Exception($response->json()['detail'] ?? 'Failed to initiate payment');

        } catch (\Exception $e) {
            Log::error('Khalti payment initiation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyPayment(Request $request)
    {
        try {
            $request->validate([
                'pidx' => 'required|string'
            ]);

            // Make request to Khalti API to verify payment
            $response = Http::withHeaders([
                'Authorization' => "Key {$this->secretKey}"
            ])->post("{$this->baseUrl}/epayment/lookup/", [
                'pidx' => $request->pidx
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Find payment record
                $payment = Payment::where('payment_details->khalti_payment_id', $request->pidx)->first();
                
                if (!$payment) {
                    throw new \Exception('Payment record not found');
                }

                if ($responseData['status'] === 'Completed') {
                    // Update payment status
                    $payment->update([
                        'status' => 'completed',
                        'payment_details' => array_merge($payment->payment_details ?? [], [
                            'verification_response' => $responseData
                        ])
                    ]);

                    // Update order status
                    $payment->order->update([
                        'payment_status' => 'paid',
                        'status' => 'completed'
                    ]);

                    return response()->json([
                        'success' => true,
                        'status' => 'completed'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'status' => $responseData['status']
                ]);
            }

            throw new \Exception($response->json()['detail'] ?? 'Failed to verify payment');

        } catch (\Exception $e) {
            Log::error('Khalti payment verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function handleReturn(Request $request)
    {
        // This method handles the return URL from Khalti
        // You can use this to show a success/failure page or redirect back to the payment manager
        return redirect()->route('admin.payment-manager.index', [
            'branch' => $request->header('X-Branch-ID')
        ])->with('success', 'Payment processed successfully');
    }
} 