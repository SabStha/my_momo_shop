<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SyncCartFromDatabase
{
    /**
     * Handle an incoming request.
     * Only sync DB→session on GET requests. POST/PUT/DELETE are cart mutations
     * that have already written fresh data — syncing would overwrite them.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only sync on GET requests — never overwrite on mutations
        if (auth('web')->check() && $request->isMethod('GET')) {
            $userCart = auth('web')->user()->getOrCreateCart();
            $dbCart = $userCart->cart_data ?? [];
            if (!empty($dbCart)) {
                session(['cart' => $dbCart]);
            }
        }

        return $next($request);
    }
}
