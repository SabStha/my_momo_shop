<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeeTimeLogController extends Controller
{
    // View all time logs for a specific employee
    public function index(Employee $employee)
    {
        $timeLogs = $employee->timeLogs()
            ->latest('clock_in')
            ->paginate(10);

        return view('admin.employees.time-logs.index', compact('employee', 'timeLogs'));
    }

    // Update a time log (edit notes)
    public function update(Request $request, Employee $employee, TimeLog $timeLog)
    {
        $validated = $request->validate([
            'clock_in' => 'required|date',
            'clock_out' => 'nullable|date|after:clock_in',
        ]);

        $timeLog->update($validated);

        return back()->with('success', 'Time log updated successfully.');
    }

    // Delete a time log
    public function destroy(TimeLog $timeLog)
    {
        $timeLog->delete();
        return back()->with('success', 'Time log deleted successfully.');
    }
} 