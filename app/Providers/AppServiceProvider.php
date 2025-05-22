<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Routing\Router;

class AppServiceProvider extends ServiceProvider
{
    public function boot(\Illuminate\Routing\Router $router): void
    {
    
        $router->aliasMiddleware('role', \Spatie\Permission\Middleware\RoleMiddleware::class);
    }
    

}
