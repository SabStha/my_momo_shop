<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Branch;

class BranchContext
{
    public function handle(Request $request, Closure $next)
    {
        // Get branch ID from query parameter or session
        $branchId = session('selected_branch_id');
        
        // If no branch is selected and we're not on the branches page, redirect to branch selection
        if (!$branchId && !$request->routeIs('admin.branches.*')) {
            return redirect()->route('admin.branches.index');
        }
        
        // If branch is selected, verify it exists and is active
        if ($branchId) {
            $branch = Branch::where('id', $branchId)
                ->where('is_active', true)
                ->first();
                
            if (!$branch) {
                session()->forget('selected_branch_id');
                return redirect()->route('admin.branches.index')
                    ->with('error', 'Selected branch is no longer available.');
            }
            
            // Share branch with all views
            view()->share('currentBranch', $branch);
        }
        
        return $next($request);
    }
} 