<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePosAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('RequirePosAccess middleware triggered', [
            'user_id' => optional(auth()->user())->id,
            'path' => $request->path(),
            'is_json' => $request->expectsJson(),
        ]);

        if (!$request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. Please login first.'], 401);
            }
            
            // For web requests, redirect to POS login
            $branchId = $request->query('branch');
            if ($branchId) {
                return redirect()->route('pos.login', ['branch' => $branchId]);
            }
            return redirect()->route('pos.login');
        }

        if (!$request->user()->hasAnyRole(['admin', 'employee.manager', 'employee.cashier'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized. POS access required.'], 403);
            }
            
            // For web requests, redirect to POS login with error
            $branchId = $request->query('branch');
            if ($branchId) {
                return redirect()->route('pos.login', ['branch' => $branchId])->with('error', 'You do not have POS access permissions.');
            }
            return redirect()->route('pos.login')->with('error', 'You do not have POS access permissions.');
        }

        // Log the POS access
        \App\Models\PosAccessLog::create([
            'user_id' => $request->user()->id,
            'access_type' => $request->expectsJson() ? 'api' : 'web',
            'action' => $request->method() . ' ' . $request->path(),
            'details' => json_encode($request->all()),
            'ip_address' => $request->ip()
        ]);

        return $next($request);
    }
}
