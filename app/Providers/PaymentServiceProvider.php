<?php

namespace App\Providers;

use App\Services\Payment\CardPaymentProcessor;
use App\Services\Payment\CashPaymentProcessor;
use App\Services\Payment\ESewaPaymentProcessor;
use App\Services\Payment\KhaltiPaymentProcessor;
use App\Services\Payment\PaymentProcessorInterface;
use App\Services\Payment\WalletPaymentProcessor;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentProcessorInterface::class, function ($app) {
            $method = request()->input('payment_method');
            return match ($method) {
                'credit_card', 'card' => new CardPaymentProcessor(),
                'wallet' => new WalletPaymentProcessor(),
                'khalti' => new KhaltiPaymentProcessor(),
                'esewa' => new ESewaPaymentProcessor(),
                'cash' => new CashPaymentProcessor(),
                default => throw new \InvalidArgumentException('Unsupported payment method: ' . $method),
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 