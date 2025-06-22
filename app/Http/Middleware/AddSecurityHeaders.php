<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Content Security Policy - Temporarily disabled for testing
        /*
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self' http://localhost:5173; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:5173 https:; " .
            "style-src 'self' 'unsafe-inline' http://localhost:5173 https:; " .
            "img-src 'self' data: https:; " .
            "font-src 'self' data: https: http://localhost:5173; " .
            "connect-src 'self' http://localhost:5173 https: ws:; " .
            "media-src 'self' https:; " .
            "object-src 'none';"
        );
        */

        // Additional security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }
} 