<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Branch;
use Illuminate\Support\Facades\View;

class BranchContext
{
    public function handle(Request $request, Closure $next)
    {
        // Get the current branch from session or default to main branch
        $branchId = session('current_branch_id');
        $branch = null;

        if ($branchId) {
            $branch = Branch::find($branchId);
        }

        if (!$branch) {
            $branch = Branch::where('is_main', true)->first();
            if ($branch) {
                session(['current_branch_id' => $branch->id]);
            }
        }

        // Share branch context with all views
        View::share('currentBranch', $branch);

        return $next($request);
    }
} 