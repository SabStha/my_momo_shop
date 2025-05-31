<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequirePosAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        $path = $request->path();
        
        // Check if user has the right role
        if (str_starts_with($path, 'pos') && !$user->hasAnyRole(['admin', 'cashier', 'employee'])) {
            return redirect()->route('home')->with('error', 'Unauthorized POS access.');
        }
        
        if (str_starts_with($path, 'payment-manager') && !$user->hasAnyRole(['admin', 'cashier'])) {
            return redirect()->route('home')->with('error', 'Unauthorized Payment Manager access.');
        }

        return $next($request);
    }
}
