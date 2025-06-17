<?php

namespace App\Services\Payment\Contracts;

interface PaymentProcessorInterface
{
    /**
     * Initialize a new payment session
     *
     * @param array $data Payment data
     * @return array Payment session data
     */
    public function initialize(array $data): array;

    /**
     * Process a payment
     *
     * @param array $data Payment data
     * @return array Payment result
     */
    public function process(array $data): array;

    /**
     * Verify a payment
     *
     * @param string $paymentId Payment ID
     * @return array Payment verification result
     */
    public function verify(string $paymentId): array;

    /**
     * Cancel a payment
     *
     * @param string $paymentId Payment ID
     * @return array Payment cancellation result
     */
    public function cancel(string $paymentId): array;
} 