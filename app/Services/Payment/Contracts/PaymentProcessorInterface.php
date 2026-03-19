<?php

namespace App\Services\Payment\Contracts;

use App\Models\Payment;
use App\Services\Payment\PaymentResponse;

interface PaymentProcessorInterface
{
    /**
     * Initialize a new payment session
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function initialize(Payment $payment): PaymentResponse;

    /**
     * Process a payment
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function process(Payment $payment): PaymentResponse;

    /**
     * Verify a payment
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function verify(Payment $payment): PaymentResponse;

    /**
     * Cancel a payment
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function cancel(Payment $payment): PaymentResponse;

    /**
     * Get redirect response for specific gateways (e.g. eSewa)
     *
     * @param Payment $payment
     * @return PaymentResponse
     */
    public function getRedirectResponse(Payment $payment): PaymentResponse;
}