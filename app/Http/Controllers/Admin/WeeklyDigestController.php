<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\WeeklyDigestService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WeeklyDigestController extends Controller
{
    protected $weeklyDigestService;

    public function __construct(WeeklyDigestService $weeklyDigestService)
    {
        $this->weeklyDigestService = $weeklyDigestService;
    }

    public function index(Request $request)
    {
        // Get branch ID from query parameter or session
        $branchId = $request->input('branch', session('selected_branch_id', 1));
        
        // Get date range from query parameters or default to last week
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : $endDate->copy()->subWeek();

        // Generate digest
        $digest = $this->weeklyDigestService->generateWeeklyDigest($branchId, $startDate, $endDate);

        // Add additional data for the view
        $data = [
            'digest' => $digest,
            'currentBranch' => \App\Models\Branch::find($branchId),
            'dateRange' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ]
        ];

        return view('admin.weekly-digest.index', $data);
    }
} 