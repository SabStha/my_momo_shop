<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'login',
        'debug-login',
        'test-minimal',
        'test-login-controller',
        '192.168.0.19/*',
        'api/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        // Only log for login-related requests to avoid spam
        if ($request->path() === 'login' || $request->routeIs('login*')) {
            \Log::info('🔒 CSRF MIDDLEWARE - LOGIN REQUEST', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'uri' => $request->getRequestUri(),
                'is_excluded' => $this->inExceptArray($request),
                'csrf_token_provided' => $request->has('_token'),
                'csrf_token' => $request->input('_token'),
                'session_token' => $request->session()->token(),
                'timestamp' => now()
            ]);
        }

        return parent::handle($request, $next);
    }
} 