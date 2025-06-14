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
                $query->where('branch_id', $request->branch);
            });

        // Get POS logs
        $posLogs = (clone $query)
            ->where('module', 'pos')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get payment logs
        $paymentLogs = (clone $query)
            ->where('module', 'payment')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.activity-logs.index', compact('posLogs', 'paymentLogs'));
    }

    public function show(ActivityLog $activityLog)
    {
        return view('admin.activity-logs.show', [
            'log' => $activityLog->load(['user', 'branch'])
        ]);
    }
} 