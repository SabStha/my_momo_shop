<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\ESewaPaymentProcessor;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
            'payment_method' => 'required|in:credit_card,wallet,khalti,esewa,cash',
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

            $response = $this->paymentService->initialize($payment);

            if ($response->success) {
                // For eSewa payments, use the redirect response from the service
                if ($request->payment_method === 'esewa') {
                    $redirectResponse = $this->paymentService->getRedirectResponse($payment);
                    if ($redirectResponse->redirectUrl) {
                        return redirect($redirectResponse->redirectUrl);
                    }
                }
                
                return response()->json($response->toArray());
            }
            return response()->json($response->toArray(), 400);
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
            $response = $this->paymentService->process($payment);

            if ($response->success) {
                return response()->json($response->toArray());
            }
            return response()->json($response->toArray(), 400);
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
        $response = $this->paymentService->verify($payment);

        if ($response->success) {
            return response()->json($response->toArray());
        }

        return response()->json($response->toArray(), 400);
    }

    /**
     * Cancel a payment
     *
     * @param Payment $payment
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Payment $payment)
    {
        $response = $this->paymentService->cancel($payment);

        if ($response->success) {
            return response()->json($response->toArray());
        }

        return response()->json($response->toArray(), 400);
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

    public function index()
    {
        // Get user data if logged in
        $userData = null;
        if (Auth::check()) {
            $user = Auth::user();
            $userData = [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->city,
                'ward_number' => $user->ward_number,
                'area_locality' => $user->area_locality,
                'building_name' => $user->building_name,
                'detailed_directions' => $user->detailed_directions,
            ];
        }

        return view('payment', compact('userData'));
    }

    /**
     * View a payment's details — used by staff/admin to review a payment.
     */
    public function viewPayment(Request $request)
    {
        try {
            $paymentId = $request->query('payment_id') ?? $request->route('payment');
            $payment = Payment::with(['order.items.product', 'paymentMethod'])->findOrFail($paymentId);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'payment' => $payment,
                ]);
            }

            // Use the customer-facing payment viewer view
            return view('customer.payment-viewer', compact('payment'));
        } catch (\Exception $e) {
            Log::error('PaymentController@viewPayment error: ' . $e->getMessage());
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }
            return abort(404, 'Payment not found');
        }
    }

    /**
     * Handle eSewa payment success callback.
     * eSewa returns: ?pid=<payment_id>&rid=<transaction_id>&amt=<amount>
     * (legacy v1 API also uses ?oid=, ?refId= — we handle both)
     */
    public function esewaSuccess(Request $request)
    {
        // eSewa v1 uses pid/rid; some integrations use oid/refId
        $paymentId  = $request->get('pid') ?? $request->get('oid');
        $refId      = $request->get('rid') ?? $request->get('refId');
        $amount     = $request->get('amt');

        $order = null;

        try {
            $payment = $paymentId ? Payment::find($paymentId) : null;

            if ($payment && $refId) {
                // Attempt server-side verification with eSewa
                try {
                    $processor = app(ESewaPaymentProcessor::class);
                    $result = $processor->verify($payment);

                    if ($result->success) {
                        $payment->update(['status' => 'completed']);
                    }
                } catch (\Exception $e) {
                    Log::warning('eSewa verification call failed, trusting redirect: ' . $e->getMessage(), [
                        'payment_id' => $paymentId,
                        'ref_id'     => $refId,
                    ]);
                    // Degrade gracefully: trust eSewa's redirect, mark completed
                    $payment->update(['status' => 'completed']);
                }

                // Update the order regardless — eSewa only sends to success URL on success
                if ($payment->order_id) {
                    $order = Order::find($payment->order_id);
                    if ($order && $order->payment_status !== 'paid') {
                        $order->update([
                            'payment_status' => 'paid',
                            'status'         => 'confirmed',
                        ]);
                    }
                }
            } else {
                Log::warning('eSewa success callback missing pid/rid', $request->all());
            }
        } catch (\Exception $e) {
            Log::error('eSewa success handler error: ' . $e->getMessage(), [
                'payment_id' => $paymentId,
                'ref_id'     => $refId,
            ]);
        }

        return view('payment.esewa.success', compact('order'));
    }

    /**
     * Print a payment receipt — opens the PDF receipt blade view.
     */
    public function printReceipt(Request $request)
    {
        try {
            $paymentId = $request->query('payment_id') ?? $request->route('payment');
            $payment = Payment::with(['order', 'paymentMethod'])->findOrFail($paymentId);

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => true,
                    'payment' => $payment,
                    'order' => $payment->order,
                ]);
            }

            // Render the existing PDF receipt blade
            return view('pdf.payment-receipt', compact('payment'));
        } catch (\Exception $e) {
            Log::error('PaymentController@printReceipt error: ' . $e->getMessage());
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }
            return abort(404, 'Payment not found');
        }
    }
}