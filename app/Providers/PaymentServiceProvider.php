<?php

namespace App\Providers;

use App\Services\Payment\CardPaymentProcessor;
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
                'credit_card' => new CardPaymentProcessor(),
                'wallet' => new WalletPaymentProcessor(),
                'khalti' => new KhaltiPaymentProcessor(),
                default => throw new \InvalidArgumentException('Unsupported payment method'),
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