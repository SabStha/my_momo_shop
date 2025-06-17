<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payment\Contracts\PaymentProcessorInterface;
use Illuminate\Support\Facades\Log;

abstract class AbstractPaymentProcessor implements PaymentProcessorInterface
{
    protected PaymentMethod $method;
    protected array $config;

    public function __construct(PaymentMethod $method)
    {
        $this->method = $method;
        $this->config = $method->config ?? [];
    }

    /**
     * Log payment activity
     *
     * @param Payment $payment
     * @param string $action
     * @param array $data
     * @return void
     */
    protected function logActivity(Payment $payment, string $action, array $data = []): void
    {
        Log::channel('payments')->info("Payment {$action}", [
            'payment_id' => $payment->id,
            'method' => $this->method->code,
            'data' => $data
        ]);
    }

    /**
     * Update payment status
     *
     * @param Payment $payment
     * @param string $status
     * @param array $data
     * @return Payment
     */
    protected function updatePaymentStatus(Payment $payment, string $status, array $data = []): Payment
    {
        $payment->status = $status;
        $payment->metadata = array_merge($payment->metadata ?? [], $data);
        
        if ($status === 'completed') {
            $payment->completed_at = now();
        } elseif ($status === 'cancelled') {
            $payment->cancelled_at = now();
        } elseif ($status === 'failed') {
            $payment->failed_at = now();
        }

        $payment->save();
        return $payment;
    }

    /**
     * Handle payment error
     *
     * @param Payment $payment
     * @param \Throwable $error
     * @return Payment
     */
    protected function handleError(Payment $payment, \Throwable $error): Payment
    {
        $this->logActivity($payment, 'error', [
            'error' => $error->getMessage(),
            'trace' => $error->getTraceAsString()
        ]);

        return $this->updatePaymentStatus($payment, 'failed', [
            'error' => $error->getMessage()
        ]);
    }
} 