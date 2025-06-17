<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initialize a new payment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function initialize(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:credit_card,wallet,khalti,cash',
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|size:3',
        ]);

        try {
            $paymentMethod = \App\Models\PaymentMethod::where('code', $request->payment_method)->first();
            if (!$paymentMethod) {
                return response()->json(['success' => false, 'message' => 'Invalid payment method.'], 400);
            }

            $payment = Payment::create([
                'order_id' => $request->order_id,
                'user_id' => auth()->id(),
                'payment_method_id' => $paymentMethod->id,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'NPR',
                'status' => 'pending',
            ]);

            $result = $this->paymentService->initialize($payment);

            if ($result['success']) {
                return response()->json($result);
            }
            return response()->json($result, 400);
        } catch (\Exception $e) {
            \Log::error('Payment initialization error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment initialization failed.'], 500);
        }
    }

    /**
     * Process a payment
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function process(Request $request, Payment $payment)
    {
        try {
            // Add any additional validation here if needed
            $result = $this->paymentService->process($payment);

            if ($result['success']) {
                return response()->json($result);
            }
            return response()->json($result, 400);
        } catch (\Exception $e) {
            \Log::error('Payment processing error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Payment processing failed.'], 500);
        }
    }

    /**
     * Verify a payment
     *
     * @param Payment $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(Payment $payment)
    {
        $result = $this->paymentService->verify($payment);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Cancel a payment
     *
     * @param Payment $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Payment $payment)
    {
        $result = $this->paymentService->cancel($payment);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Show payment receipt
     *
     * @param Payment $payment
     * @return \Illuminate\Contracts\View\View
     */
    public function receipt(Payment $payment)
    {
        $payment->load(['order.user', 'paymentMethod']);
        return view('pdf.payment-receipt', compact('payment'));
    }
} 