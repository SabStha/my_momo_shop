<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IsAdmin
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
        Log::info('IsAdmin middleware called', [
            'user' => auth()->user() ? [
                'id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'roles' => auth()->user()->getRoleNames()
            ] : 'No user logged in',
            'path' => $request->path(),
            'method' => $request->method()
        ]);

        if (!auth()->check()) {
            Log::warning('User not authenticated');
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'cashier'])) {
            Log::warning('User not authorized as admin or cashier', [
                'user_id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'roles' => auth()->user()->getRoleNames()
            ]);
            return redirect()->route('home')->with('error', 'You do not have permission to access this page.');
        }

        Log::info('User authorized as admin or cashier', [
            'user_id' => auth()->user()->id,
            'email' => auth()->user()->email,
            'roles' => auth()->user()->getRoleNames()
        ]);

        return $next($request);
    }
} 