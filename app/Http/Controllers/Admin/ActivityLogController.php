<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user', 'branch'])
            ->when($request->filled('branch'), function ($query) use ($request) {
                $query->where('subject_id', $request->branch)
                      ->where('subject_type', 'App\Models\Branch');
            });

        // Get all activity logs
        $activityLogs = $query
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.activity-logs.index', compact('activityLogs'));
    }

    public function show(ActivityLog $activityLog)
    {
        return view('admin.activity-logs.show', [
            'log' => $activityLog->load(['user', 'branch'])
        ]);
    }
} 