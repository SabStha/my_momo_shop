<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsCreator
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && (auth()->user()->is_creator || auth()->user()->creator)) {
            return $next($request);
        }
        abort(403, 'Unauthorized');
    }
} 