<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Router;
use Spatie\Permission\Middlewares\RoleMiddleware;

class AppServiceProvider extends ServiceProvider
{
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
