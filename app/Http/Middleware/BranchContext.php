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
            'offers.*',
            'ai-popup.*',
            'search',
            'products.*',
            'pos.login',
            'pos.login.submit',
            'creator.register',
            'creator.register.submit',
            'creator.dashboard',
            'creator-dashboard.*',
            'admin.branches.*',
            'investor.*',
            'profile.edit',
            'profile.update',
            'profile.destroy',
            'profile.picture',
            'cart',
            'cart.*',
            'checkout',
            'checkout.*',
            'statistics',
            'reviews.store',
        ];

        // List of paths that should be excluded from branch context
        $excludedPaths = [
            'login',
            'logout',
            'register',
            'password',
            'pos/login',
            'topup/login',
            'investor',
            'profile',
            'cart',
            'checkout',
            'reviews',
            'offers',
            'ai-popup',
        ];

        // Skip branch check for public routes
        if ($request->routeIs($publicRoutes)) {
            Log::info('Skipping branch context for public route', [
                'route' => $request->route() ? $request->route()->getName() : 'null',
                'path' => $request->path()
            ]);
            return $next($request);
        }

        // Skip branch check for excluded paths
        foreach ($excludedPaths as $excludedPath) {
            if (str_starts_with($request->path(), $excludedPath)) {
                Log::info('Skipping branch context for excluded path', [
                    'route' => $request->route() ? $request->route()->getName() : 'null',
                    'path' => $request->path(),
                    'excluded_path' => $excludedPath
                ]);
                return $next($request);
            }
        }

        // If user is not authenticated, let the auth middleware handle it
        if (!Auth::check()) {
            return $next($request);
        }

        // Get branch ID from query parameter or session
        $branchId = $request->query('branch') ?? session('selected_branch_id');
        
        // If no branch is selected and we're not on the branches page or login page, redirect to branch selection
        if (!$branchId && !$request->routeIs(['admin.branches.*', 'login', 'logout', 'admin.dashboard', 'investor.*']) && $request->path() !== 'login') {
            Log::info('No branch selected, redirecting to branch selection', [
                'route' => $request->route() ? $request->route()->getName() : 'null',
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
                session()->forget(['selected_branch_id', 'selected_branch']);
                Log::info('Selected branch not found or inactive', [
                    'branch_id' => $branchId,
                    'route' => $request->route() ? $request->route()->getName() : 'null',
                    'path' => $request->path()
                ]);
                return redirect()->route('admin.branches.index')
                    ->with('error', 'Selected branch is no longer available.');
            }
            
            // Remove selected_branch from session if present
            session()->forget(['selected_branch_id', 'selected_branch']);
            // Only set selected_branch_id, not selected_branch
            session(['selected_branch_id' => $branch->id]);
            
            // Share branch with all views
            view()->share('currentBranch', $branch);
        }
        
        return $next($request);
    }
} 