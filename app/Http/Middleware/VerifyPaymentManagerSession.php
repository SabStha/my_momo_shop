<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyPaymentManagerSession
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
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Please login first.'], 401);
            }
            return redirect()->route('login');
        }

        // Check if user has payment manager access
        $user = Auth::user();
        if (!$user->hasRole(['admin', 'cashier', 'payment_manager'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Payment manager access required.'], 403);
            }
            return redirect()->back()->with('error', 'Unauthorized. Payment manager access required.');
        }

        return $next($request);
    }
} 