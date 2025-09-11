<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePaymentAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('RequirePaymentAccess middleware triggered', [
            'user_id' => optional(auth()->user())->id,
            'path' => $request->path(),
            'is_json' => $request->expectsJson(),
        ]);

        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Please login first.'], 401);
            }
            
            // For web requests, redirect to payment login
            $branchId = $request->query('branch');
            if ($branchId) {
                return redirect()->route('payment.login', ['branch' => $branchId]);
            }
            return redirect()->route('payment.login');
        }

        if (!$request->user()->hasAnyRole(['admin', 'employee.manager', 'employee.cashier'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Payment management access required.'], 403);
            }
            
            // For web requests, redirect to payment login with error
            $branchId = $request->query('branch');
            if ($branchId) {
                return redirect()->route('payment.login', ['branch' => $branchId])->with('error', 'You do not have payment management access permissions.');
            }
            return redirect()->route('payment.login')->with('error', 'You do not have payment management access permissions.');
        }

        // Check if payment session is authenticated
        if (!session('payment_authenticated')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Payment session expired. Please login again.'], 401);
            }
            
            $branchId = $request->query('branch');
            if ($branchId) {
                return redirect()->route('payment.login', ['branch' => $branchId])->with('error', 'Payment session expired. Please login again.');
            }
            return redirect()->route('payment.login')->with('error', 'Payment session expired. Please login again.');
        }

        // Log the payment access
        \App\Models\PosAccessLog::create([
            'user_id' => $request->user()->id,
            'access_type' => 'payment_management',
            'action' => $request->method() . ' ' . $request->path(),
            'details' => json_encode($request->all()),
            'ip_address' => $request->ip()
        ]);

        return $next($request);
    }
}
