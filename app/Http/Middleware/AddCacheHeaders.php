<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddCacheHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip if response is already cached
        if ($response->headers->has('Cache-Control')) {
            return $response;
        }

        $path = $request->path();
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        // Static assets - long-term cache
        if (in_array($extension, ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'webp', 'avif', 'woff', 'woff2', 'ttf', 'eot'])) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('Vary', 'Accept-Encoding');
            $response->headers->set('Content-Encoding', 'gzip');
        }
        // Build assets - long-term cache with versioning
        elseif (str_starts_with($path, 'build/')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
            $response->headers->set('Vary', 'Accept-Encoding');
        }
        // PWA assets
        elseif (in_array($path, ['manifest.webmanifest', 'sw.js'])) {
            $response->headers->set('Cache-Control', 'public, max-age=86400'); // 24 hours
            $response->headers->set('Content-Type', $path === 'manifest.webmanifest' ? 'application/manifest+json' : 'application/javascript');
        }
        // HTML pages - no cache
        elseif ($extension === 'html' || $extension === '') {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
        // API responses - short cache
        elseif (str_starts_with($path, 'api/')) {
            $response->headers->set('Cache-Control', 'private, max-age=300'); // 5 minutes
        }

        return $response;
    }
} 