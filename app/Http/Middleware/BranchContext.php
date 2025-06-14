<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BranchContext
{
    public function handle(Request $request, Closure $next)
    {
        // List of routes that don't require branch context
        $publicRoutes = [
            'home',
            'login',
            'register',
            'register.submit',
            'password.*',
            'about',
            'contact',
            'terms',
            'privacy',
            'menu',
            'bulk',
            'finds',
            'public.leaderboard',
            'offers',
            'search',
            'products.*',
            'pos.login',
            'pos.login.submit',
            'creator.register',
            'creator.register.submit',
            'creator.dashboard',
            'creator-dashboard.*',
        ];

        // Skip branch check for public routes
        if ($request->routeIs($publicRoutes)) {
            Log::info('Skipping branch context for public route', [
                'route' => $request->route()->getName(),
                'path' => $request->path()
            ]);
            return $next($request);
        }

        // If user is not authenticated, let the auth middleware handle it
        if (!Auth::check()) {
            return $next($request);
        }

        // Get branch ID from query parameter or session
        $branchId = $request->query('branch') ?? session('selected_branch_id');
        
        // If no branch is selected and we're not on the branches page, redirect to branch selection
        if (!$branchId && !$request->routeIs('admin.branches.*')) {
            Log::info('No branch selected, redirecting to branch selection', [
                'route' => $request->route()->getName(),
                'path' => $request->path()
            ]);
            return redirect()->route('admin.branches.index');
        }
        
        // If branch is selected, verify it exists and is active
        if ($branchId) {
            $branch = Branch::where('id', $branchId)
                ->where('is_active', true)
                ->first();
                
            if (!$branch) {
                session()->forget('selected_branch_id');
                Log::info('Selected branch not found or inactive', [
                    'branch_id' => $branchId,
                    'route' => $request->route()->getName(),
                    'path' => $request->path()
                ]);
                return redirect()->route('admin.branches.index')
                    ->with('error', 'Selected branch is no longer available.');
            }
            
            // Share branch with all views
            view()->share('currentBranch', $branch);
        }
        
        return $next($request);
    }
} 