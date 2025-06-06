<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only apply CSP in production
        if (app()->environment('production')) {
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://unpkg.com; " .
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.bunny.net; " .
                "img-src 'self' data: https:; " .
                "font-src 'self' https://fonts.bunny.net; " .
                "connect-src 'self' https://fonts.bunny.net; " .
                "frame-src 'self'; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self'; " .
                "frame-ancestors 'none'; " .
                "block-all-mixed-content; " .
                "upgrade-insecure-requests;"
            );
        } else {
            // In development, allow Vite's development server
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://unpkg.com; " .
                "style-src 'self' 'unsafe-inline' http://localhost:5173 https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.bunny.net; " .
                "img-src 'self' data: https:; " .
                "font-src 'self' https://fonts.bunny.net; " .
                "connect-src 'self' http://localhost:5173 https://fonts.bunny.net; " .
                "frame-src 'self'; " .
                "object-src 'none'; " .
                "base-uri 'self'; " .
                "form-action 'self'; " .
                "frame-ancestors 'none'; " .
                "block-all-mixed-content; " .
                "upgrade-insecure-requests;"
            );
        }

        return $response;
    }
} 