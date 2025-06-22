<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Router;
use Spatie\Permission\Middlewares\RoleMiddleware;
use App\Services\Payment\Contracts\PaymentProcessorInterface;
use App\Services\Payment\CardPaymentProcessor;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind the PaymentProcessorInterface to a concrete implementation
        $this->app->bind(PaymentProcessorInterface::class, CardPaymentProcessor::class);
    }

    public function boot(Router $router): void
    {
        // Fix for MySQL key length limit error
        Schema::defaultStringLength(191);

        // Register the Spatie role middleware
        $router->aliasMiddleware('role', RoleMiddleware::class);

        // Force HTTPS for all URLs only in production
        if (app()->environment('production')) {
            \URL::forceScheme('https');
        }
    }
}
