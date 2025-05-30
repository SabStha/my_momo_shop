<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminClockController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $timeLogs = TimeLog::with('employee.user')
            ->whereDate('clock_in', $date)
            ->orderBy('clock_in', 'desc')
            ->get();

        if (request()->ajax()) {
            return response()->json([
                'html' => view('admin.clock._time_logs', compact('timeLogs'))->render()
            ]);
        }

        // Get last 7 days for the date selector
        $lastSevenDays = collect(range(0, 6))->map(function($day) {
            return now()->subDays($day)->format('Y-m-d');
        });

        return view('desktop.admin.clock.index', compact('timeLogs', 'date', 'lastSevenDays'));
    }

    public function edit(Request $request, TimeLog $timeLog)
    {
        $data = $request->only(['clock_in', 'clock_out', 'break_start', 'break_end', 'notes']);

        // Only validate fields that are present
        $rules = [];
        if ($request->has('clock_in')) $rules['clock_in'] = 'nullable|date';
        if ($request->has('clock_out')) $rules['clock_out'] = 'nullable|date';
        if ($request->has('break_start')) $rules['break_start'] = 'nullable|date';
        if ($request->has('break_end')) $rules['break_end'] = 'nullable|date';
        if ($request->has('notes')) $rules['notes'] = 'nullable|string';

        $validated = $request->validate($rules);

        $timeLog->update($validated);

        return response()->json(['message' => 'Time log updated successfully.']);
    }

    public function report(Request $request)
    {
        $employeeId = $request->get('employee_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $reportType = $request->get('report_type', 'weekly');

        if ($reportType === 'monthly') {
            $year = $request->get('year', now()->year);
            $month = $request->get('month', now()->month);
            $timeLogs = TimeLog::getMonthlyReport($employeeId, $year, $month);
        } else {
            $timeLogs = TimeLog::getWeeklyReport($employeeId, $startDate, $endDate);
        }

        $employees = Employee::with('user')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.clock._report', compact('timeLogs', 'employees'))->render()
            ]);
        }

        return view('admin.clock.report', compact('timeLogs', 'employees'));
    }

    public function search(Request $request)
    {
        $term = $request->get('term');
        
        $employees = Employee::with('user')
            ->whereHas('user', function($query) use ($term) {
                $query->where('name', 'like', "%{$term}%");
            })
            ->orWhere('id', 'like', "%{$term}%")
            ->get()
            ->map(function($employee) {
                return [
                    'value' => $employee->id,
                    'label' => $employee->user->name
                ];
            });

        return response()->json($employees);
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_search' => 'required|string'
        ]);

        $employee = $this->findEmployee($request->employee_search);
        
        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        if ($employee->isCurrentlyWorking()) {
            return response()->json(['message' => 'Employee is already clocked in.'], 400);
        }

        $employee->timeLogs()->create([
            'clock_in' => now(),
            'status' => 'active'
        ]);

        return response()->json(['message' => 'Employee clocked in successfully.']);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'employee_search' => 'required|string'
        ]);

        $employee = $this->findEmployee($request->employee_search);
        
        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $currentShift = $employee->getCurrentShift();

        if (!$currentShift) {
            return response()->json(['message' => 'Employee is not currently clocked in.'], 400);
        }

        if ($currentShift->status === 'on_break') {
            return response()->json(['message' => 'Employee is currently on break. Please end the break first before clocking out.'], 400);
        }

        if ($currentShift->status === 'completed') {
            return response()->json(['message' => 'Employee is already clocked out.'], 400);
        }

        $currentShift->update([
            'clock_out' => now(),
            'status' => 'completed'
        ]);

        return response()->json(['message' => 'Employee clocked out successfully.']);
    }

    public function startBreak(Request $request)
    {
        $request->validate([
            'employee_search' => 'required|string'
        ]);

        $employee = $this->findEmployee($request->employee_search);
        
        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $currentShift = $employee->getCurrentShift();

        if (!$currentShift) {
            return response()->json(['message' => 'Employee is not currently clocked in.'], 400);
        }

        if ($currentShift->status === 'on_break') {
            return response()->json(['message' => 'Employee is already on break.'], 400);
        }

        if ($currentShift->status === 'completed') {
            return response()->json(['message' => 'Employee is already clocked out.'], 400);
        }

        $currentShift->update([
            'break_start' => now(),
            'status' => 'on_break'
        ]);

        return response()->json(['message' => 'Break started successfully.']);
    }

    public function endBreak(Request $request)
    {
        $request->validate([
            'employee_search' => 'required|string'
        ]);

        $employee = $this->findEmployee($request->employee_search);
        
        if (!$employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $currentShift = $employee->getCurrentShift();

        if (!$currentShift) {
            return response()->json(['message' => 'Employee is not currently clocked in.'], 400);
        }

        if ($currentShift->status !== 'on_break') {
            return response()->json(['message' => 'Employee is not on break.'], 400);
        }

        if ($currentShift->status === 'completed') {
            return response()->json(['message' => 'Employee is already clocked out.'], 400);
        }

        $currentShift->update([
            'break_end' => now(),
            'status' => 'active'
        ]);

        return response()->json(['message' => 'Break ended successfully.']);
    }

    private function findEmployee($search)
    {
        return Employee::with('user')
            ->whereHas('user', function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orWhere('id', 'like', "%{$search}%")
            ->first();
    }
} 