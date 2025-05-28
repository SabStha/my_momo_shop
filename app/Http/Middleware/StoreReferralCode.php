<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Creator;

class StoreReferralCode
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->has('ref')) {
            $code = $request->get('ref');
            
            // Check if creator exists
            $creator = Creator::where('code', $code)->first();
            
            if ($creator) {
                session(['referral_code' => $code]);
            } else {
                session()->flash('referral_error', 'Invalid referral code: ' . $code);
            }
        }

        return $next($request);
    }
} 