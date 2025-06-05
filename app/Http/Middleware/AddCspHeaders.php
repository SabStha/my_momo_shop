<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddCspHeaders
{
    public function handle(Request $request, Closure $next)
    {
        // Temporarily disable CSP headers
        return $next($request);

        // Original CSP code (commented out for now)
        /*
        $response = $next($request);

        // Only add CSP headers for non-production environments
        if (app()->environment('local', 'development')) {
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* https://localhost:*; " .
                "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; " .
                "img-src 'self' data: https:; " .
                "font-src 'self' https://cdnjs.cloudflare.com; " .
                "connect-src 'self' ws://localhost:* wss://localhost:* http://localhost:* https://localhost:*;"
            );
        }

        return $response;
        */
    }
} 