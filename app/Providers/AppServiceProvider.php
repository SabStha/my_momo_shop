<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\View;
use Spatie\Permission\Middlewares\RoleMiddleware;
use App\Services\Payment\Contracts\PaymentProcessorInterface;
use App\Services\Payment\CardPaymentProcessor;
use App\Models\Offer;

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

        // Share activeOffers with all views for the top navigation
        View::composer('*', function ($view) {
            try {
                // Get active offers for the top navigation
                $activeOffers = Offer::active()->latest()->take(6)->get();
                
                // If user is logged in, get personalized offers and claimed offers
                if (auth()->check()) {
                    $user = auth()->user();
                    
                    // Get personalized offers
                    $personalizedOffers = Offer::active()
                        ->personalized()
                        ->forUser($user->id)
                        ->latest()
                        ->take(3)
                        ->get();
                    
                    // Get user's claimed offers (both active and used)
                    $claimedOffers = $user->offerClaims()
                        ->with(['offer'])
                        ->orderBy('claimed_at', 'desc')
                        ->get()
                        ->map(function($claim) {
                            return $claim->offer;
                        })
                        ->filter(function($offer) {
                            return $offer && $offer->is_active;
                        });
                    
                    // Merge all offers: personalized + claimed + general offers
                    $allOffers = $personalizedOffers->merge($claimedOffers)->merge($activeOffers);
                    
                    // Remove duplicates and take the latest 8 offers
                    $activeOffers = $allOffers->unique('id')->take(8);
                }
                
                $view->with('activeOffers', $activeOffers);
            } catch (\Exception $e) {
                // If there's an error, provide empty collection
                $view->with('activeOffers', collect());
            }
        });
    }
}
