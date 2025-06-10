<?php

namespace App\Traits;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

trait BranchAware
{
    /**
     * Get the branch relationship
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Scope a query to only include records for the current branch
     */
    public function scopeForCurrentBranch($query)
    {
        $branchId = session('current_branch_id');
        if ($branchId) {
            return $query->where('branch_id', $branchId);
        }
        return $query;
    }

    /**
     * Scope a query to only include records for all branches
     */
    public function scopeForAllBranches($query)
    {
        return $query;
    }

    /**
     * Get the current branch
     */
    public static function currentBranch()
    {
        $branchId = session('current_branch_id');
        if ($branchId) {
            return Branch::find($branchId);
        }
        return Branch::where('is_main', true)->first();
    }

    /**
     * Check if the model belongs to the current branch
     */
    public function belongsToCurrentBranch()
    {
        $currentBranchId = session('current_branch_id');
        return $this->branch_id === $currentBranchId;
    }

    /**
     * Check if the model belongs to the main branch
     */
    public function belongsToMainBranch()
    {
        return $this->branch && $this->branch->is_main;
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForMainBranch($query)
    {
        return $query->whereHas('branch', function ($q) {
            $q->where('is_main', true);
        });
    }

    public static function getCurrentBranch()
    {
        $branchId = session('current_branch_id');
        if ($branchId) {
            return Branch::find($branchId);
        }
        return Branch::where('is_main', true)->first();
    }

    protected function getBranchFromRequest(Request $request)
    {
        // First try to get branch from query parameter
        $branchId = $request->query('branch');
        
        // If not in query, try session
        if (!$branchId) {
            $branchId = session('selected_branch_id');
        }
        
        if (!$branchId) {
            return null;
        }
        
        return Branch::where('id', $branchId)
            ->where('is_active', true)
            ->first();
    }
    
    protected function getBranchIdFromRequest(Request $request)
    {
        // First try to get branch from query parameter
        $branchId = $request->query('branch');
        
        // If not in query, try session
        if (!$branchId) {
            $branchId = session('selected_branch_id');
        }
        
        return $branchId;
    }
    
    protected function validateBranchAccess(Request $request)
    {
        $branchId = $this->getBranchIdFromRequest($request);
        
        if (!$branchId) {
            return false;
        }
        
        $user = $request->user();
        return $user->hasBranchAccess($branchId);
    }
    
    protected function getCurrentBranchId()
    {
        return session('selected_branch_id');
    }
    
    protected function setCurrentBranchId($branchId)
    {
        session(['selected_branch_id' => $branchId]);
    }
    
    protected function clearCurrentBranchId()
    {
        session()->forget('selected_branch_id');
    }
} 