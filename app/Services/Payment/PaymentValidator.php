<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaymentValidator
{
    public function validatePayment(Payment $payment): void
    {
        $rules = [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'payment_details' => ['required', 'json'],
        ];

        $validator = Validator::make($payment->toArray(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Validate payment method specific details
        $this->validatePaymentMethodDetails($payment);
    }

    private function validatePaymentMethodDetails(Payment $payment): void
    {
        $method = PaymentMethod::findOrFail($payment->payment_method_id);
        $details = json_decode($payment->payment_details, true);

        switch ($method->code) {
            case 'card':
                $this->validateCardDetails($details);
                break;
            case 'wallet':
                $this->validateWalletDetails($payment);
                break;
            case 'khalti_qr':
                // QR payments don't need initial validation
                break;
            default:
                throw new \InvalidArgumentException("Unsupported payment method: {$method->code}");
        }
    }

    private function validateCardDetails(array $details): void
    {
        $validator = Validator::make($details, [
            'card_number' => ['required', 'string', 'size:16'],
            'expiry_month' => ['required', 'integer', 'between:1,12'],
            'expiry_year' => ['required', 'integer', 'min:' . date('Y')],
            'cvv' => ['required', 'string', 'size:3'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function validateWalletDetails(Payment $payment): void
    {
        $wallet = $payment->user->wallet;
        
        if (!$wallet) {
            throw new \InvalidArgumentException('User does not have a wallet');
        }

        if ($wallet->balance < $payment->amount) {
            throw new \InvalidArgumentException('Insufficient wallet balance');
        }
    }
} 