<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePosAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized. Please login first.'], 401);
        }

        if (!$request->user()->hasAnyRole(['admin', 'employee.manager', 'employee.cashier'])) {
            return response()->json(['message' => 'Unauthorized. POS access required.'], 403);
        }

        // Log the POS access
        \App\Models\PosAccessLog::create([
            'user_id' => $request->user()->id,
            'access_type' => 'api',
            'action' => $request->method() . ' ' . $request->path(),
            'details' => json_encode($request->all()),
            'ip_address' => $request->ip()
        ]);

        return $next($request);
    }
}
