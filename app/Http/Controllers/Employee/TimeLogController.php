<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\TimeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeLogController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        $timeLogs = $employee->timeLogs()
            ->orderBy('clock_in', 'desc')
            ->paginate(15);

        return view('employee.time-logs.index', compact('timeLogs'));
    }

    public function show(TimeLog $timeLog)
    {
        $this->authorize('view', $timeLog);
        return view('employee.time-logs.show', compact('timeLog'));
    }

    public function update(Request $request, TimeLog $timeLog)
    {
        $this->authorize('update', $timeLog);
        
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $timeLog->update([
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Time log notes updated successfully.');
    }
} 