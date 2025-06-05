<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyPaymentManagerSession
{
    public function handle(Request $request, Closure $next)
    {
        // Check if payment manager session exists and hasn't expired
        if (!session()->has('payment_verified') || 
            !session()->has('payment_verified_at') ||
            !session()->has('payment_verified_user_id')) {
            return redirect()->route('payment-manager.login');
        }

        // Check if session has expired (30 minutes)
        $verifiedAt = session('payment_verified_at');
        if (now()->timestamp - $verifiedAt > 1800) { // 1800 seconds = 30 minutes
            session()->forget(['payment_verified', 'payment_verified_at', 'payment_verified_user_id']);
            return redirect()->route('payment-manager.login')
                ->with('error', 'Your session has expired. Please login again.');
        }

        // Verify the user still exists and has the required role
        $user = Auth::user();
        if (!$user || $user->id !== session('payment_verified_user_id') || 
            !$user->hasRole(['admin', 'cashier'])) {
            session()->forget(['payment_verified', 'payment_verified_at', 'payment_verified_user_id']);
            return redirect()->route('payment-manager.login')
                ->with('error', 'Your session is invalid. Please login again.');
        }

        return $next($request);
    }
} 