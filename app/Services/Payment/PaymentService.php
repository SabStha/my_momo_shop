<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payment\Contracts\PaymentProcessorInterface;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $processor;

    public function __construct(PaymentProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Initialize a new payment
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function initialize(Payment $payment): PaymentResponse
    {
        try {
            return $this->processor->initialize($payment);
        } catch (\Exception $e) {
            Log::error('Payment initialization failed: ' . $e->getMessage());
            return PaymentResponse::failure('Payment initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Process a payment
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function process(Payment $payment): PaymentResponse
    {
        try {
            return $this->processor->process($payment);
        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage());
            return PaymentResponse::failure('Payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify a payment
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function verify(Payment $payment): PaymentResponse
    {
        try {
            return $this->processor->verify($payment);
        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return PaymentResponse::failure('Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a payment
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function cancel(Payment $payment): PaymentResponse
    {
        try {
            return $this->processor->cancel($payment);
        } catch (\Exception $e) {
            Log::error('Payment cancellation failed: ' . $e->getMessage());
            return PaymentResponse::failure('Payment cancellation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get redirect response for specific gateways (e.g. eSewa)
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function getRedirectResponse(Payment $payment): PaymentResponse
    {
        try {
            return $this->processor->getRedirectResponse($payment);
        } catch (\Exception $e) {
            Log::error('Payment redirect failed: ' . $e->getMessage());
            return PaymentResponse::failure('Payment redirect failed: ' . $e->getMessage());
        }
    }
}
 