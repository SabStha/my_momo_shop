<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WalletAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            Log::warning('WalletAuthMiddleware: User not authenticated', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return redirect()->route('login');
        }

        // Check if user has admin role
        if (!Auth::user()->hasRole('admin')) {
            Log::warning('WalletAuthMiddleware: User does not have admin role', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'roles' => Auth::user()->getRoleNames(),
                'ip' => $request->ip()
            ]);
            return redirect()->route('admin.wallet.topup.login')
                           ->with('error', 'You must be an admin to access this feature.');
        }

        // Check if user has wallet authentication
        if (!Session::has('wallet_authenticated')) {
            Log::warning('WalletAuthMiddleware: No wallet authentication', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'ip' => $request->ip()
            ]);
            return redirect()->route('admin.wallet.topup.login')
                           ->with('error', 'Please authenticate to access wallet features.');
        }

        // Check if wallet authentication has expired (10 minutes)
        $authTime = Session::get('wallet_auth_time');
        if ($authTime && Carbon::parse($authTime)->addMinutes(10)->isPast()) {
            Log::warning('WalletAuthMiddleware: Wallet authentication expired', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'auth_time' => $authTime,
                'ip' => $request->ip()
            ]);
            Session::forget(['wallet_authenticated', 'wallet_auth_time']);
            return redirect()->route('admin.wallet.topup.login')
                           ->with('error', 'Your wallet session has expired. Please authenticate again.');
        }

        // Update the last activity time
        Session::put('wallet_last_activity', now());

        return $next($request);
    }
} 