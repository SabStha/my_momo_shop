<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePosAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->hasAnyRole(['admin', 'employee.manager', 'employee.cashier'])) {
            return response()->json(['message' => 'Unauthorized. POS access required.'], 403);
        }

        return $next($request);
    }
}
