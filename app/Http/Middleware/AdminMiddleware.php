<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized access'], 403);
            }
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
} 