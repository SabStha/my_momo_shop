<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // If user is admin, redirect to admin dashboard
                if (Auth::user()->hasRole('admin')) {
                    return redirect('/admin/dashboard');
                }
                
                // If user has other roles, redirect to appropriate dashboard
                if (Auth::user()->hasRole('creator')) {
                    return redirect('/creator/dashboard');
                }
                
                // Default redirect for authenticated users
                return redirect('/dashboard');
            }
        }

        return $next($request);
    }
} 