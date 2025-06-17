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
     * @param array $data
     * @return array
     */
    public function initialize(Payment $payment): array
    {
        try {
            return $this->processor->initialize($payment);
        } catch (\Exception $e) {
            Log::error('Payment initialization failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment initialization failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process a payment
     *
     * @param array $data
     * @return array
     */
    public function process(Payment $payment): array
    {
        try {
            return $this->processor->process($payment);
        } catch (\Exception $e) {
            Log::error('Payment processing failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify a payment
     *
     * @param string $paymentId
     * @return array
     */
    public function verify(Payment $payment): array
    {
        try {
            return $this->processor->verify($payment);
        } catch (\Exception $e) {
            Log::error('Payment verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel a payment
     *
     * @param string $paymentId
     * @return array
     */
    public function cancel(Payment $payment): array
    {
        try {
            return $this->processor->cancel($payment);
        } catch (\Exception $e) {
            Log::error('Payment cancellation failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment cancellation failed: ' . $e->getMessage(),
            ];
        }
    }
} 