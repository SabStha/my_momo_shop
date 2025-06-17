<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WeeklyDigestService;
use App\Models\Branch;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WeeklyDigestController extends Controller
{
    protected $weeklyDigestService;

    public function __construct(WeeklyDigestService $weeklyDigestService)
    {
        $this->weeklyDigestService = $weeklyDigestService;
    }

    public function index(Request $request, $branchId = null)
    {
        // Get branch ID from parameter, query string, or session
        $branchId = $branchId ?? $request->input('branch', session('selected_branch_id'));
        
        // Validate branch
        $branch = Branch::findOrFail($branchId);
        
        // Get date range from request or default to current week
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $endDate->copy()->subWeek();

        // Generate digest
        $digest = $this->weeklyDigestService->generateWeeklyDigest($branchId, $startDate, $endDate);

        // Return view with data
        return view('admin.weekly-digest.index', [
            'digest' => $digest,
            'currentBranch' => $branch,
            'dateRange' => [
                'start' => $startDate,
                'end' => $endDate
            ]
        ]);
    }
} 