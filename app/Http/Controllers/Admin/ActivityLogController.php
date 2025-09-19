<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = ActivityLog::query();
            
            // Add relationships only if they exist
            $query->with(['user', 'branch']);
            
            // Filter by branch if requested
            if ($request->filled('branch')) {
                $query->where('subject_id', $request->branch)
                      ->where('subject_type', 'App\Models\Branch');
            }

            // Get all activity logs
            $activityLogs = $query
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return view('admin.activity-logs.index', compact('activityLogs'));
        } catch (\Exception $e) {
            \Log::error('ActivityLogController error: ' . $e->getMessage());
            return view('admin.activity-logs.index', ['activityLogs' => collect()]);
        }
    }

    public function show(ActivityLog $activityLog)
    {
        try {
            return view('admin.activity-logs.show', [
                'log' => $activityLog->load(['user', 'branch'])
            ]);
        } catch (\Exception $e) {
            \Log::error('ActivityLogController show error: ' . $e->getMessage());
            return redirect()->route('admin.activity-logs.index')->with('error', 'Activity log not found');
        }
    }
} 