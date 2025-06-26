<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Branch;

class ClockController extends Controller
{
    public function index()
    {
        // Ensure a branch is selected
        $this->ensureBranchSelected();
        
        $branch = Branch::find(session('selected_branch_id'));
        $employees = Employee::with('user')
            ->where('branch_id', $branch->id)
            ->get();

        $timeLogs = TimeLog::with(['employee.user'])
            ->whereHas('employee', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->whereDate('date', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.clock.index', compact('employees', 'timeLogs'));
    }

    private function ensureBranchSelected()
    {
        if (!session('selected_branch_id')) {
            $defaultBranch = Branch::where('is_main', true)->first() ?? Branch::first();
            if ($defaultBranch) {
                session(['selected_branch_id' => $defaultBranch->id]);
            }
        }
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $branch = Branch::find(session('selected_branch_id'));
        
        if (!$branch) {
            return response()->json([
                'success' => false,
                'message' => 'No branch selected'
            ], 400);
        }
        
        // Check if already clocked in
        $existingLog = TimeLog::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->whereNull('clock_out')
            ->first();

        if ($existingLog) {
            return response()->json([
                'success' => false,
                'message' => 'Employee is already clocked in'
            ]);
        }

        $timeLog = TimeLog::create([
            'employee_id' => $employee->id,
            'user_id' => Auth::id(),
            'branch_id' => $branch->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now(),
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clocked in successfully',
            'data' => $timeLog
        ]);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        
        $timeLog = TimeLog::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->whereNull('clock_out')
            ->first();

        if (!$timeLog) {
            return response()->json([
                'success' => false,
                'message' => 'No active clock-in found'
            ]);
        }

        $timeLog->update([
            'clock_out' => Carbon::now(),
            'status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clocked out successfully',
            'data' => $timeLog
        ]);
    }

    public function startBreak(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        
        $timeLog = TimeLog::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->whereNull('clock_out')
            ->first();

        if (!$timeLog) {
            return response()->json([
                'success' => false,
                'message' => 'No active clock-in found'
            ]);
        }

        if ($timeLog->break_start) {
            return response()->json([
                'success' => false,
                'message' => 'Break already started'
            ]);
        }

        $timeLog->update([
            'break_start' => Carbon::now(),
            'status' => 'on_break'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Break started successfully',
            'data' => $timeLog
        ]);
    }

    public function endBreak(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        
        $timeLog = TimeLog::where('employee_id', $employee->id)
            ->whereDate('date', Carbon::today())
            ->whereNull('clock_out')
            ->first();

        if (!$timeLog) {
            return response()->json([
                'success' => false,
                'message' => 'No active clock-in found'
            ]);
        }

        if (!$timeLog->break_start) {
            return response()->json([
                'success' => false,
                'message' => 'No active break found'
            ]);
        }

        $breakEnd = Carbon::now();
        $breakDuration = $breakEnd->diffInMinutes($timeLog->break_start);

        $timeLog->update([
            'break_end' => $breakEnd,
            'break_duration' => $breakDuration,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Break ended successfully',
            'data' => $timeLog
        ]);
    }

    public function searchEmployees(Request $request)
    {
        // Try to get branch from session first, then from query parameter
        $branchId = session('selected_branch_id');
        if (!$branchId && $request->has('branch')) {
            $branchId = $request->input('branch');
        }
        
        $query = $request->input('query');
        if (empty($query)) {
            return response()->json([]);
        }

        $employees = Employee::with('user')
            ->where(function($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId);
                } else {
                    // If no branch is selected, include employees with NULL branch_id
                    $q->whereNull('branch_id');
                }
            })
            ->whereHas('user', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->user->name,
                    'email' => $employee->user->email
                ];
            });

        return response()->json($employees);
    }

    public function getTimeLogs(Request $request)
    {
        // Ensure a branch is selected
        $this->ensureBranchSelected();
        
        $branch = Branch::find(session('selected_branch_id'));
        if (!$branch) {
            return response()->json(['error' => 'No branches available'], 400);
        }

        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        
        $timeLogs = TimeLog::with(['employee.user'])
            ->whereHas('employee', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->whereDate('date', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($timeLogs);
    }

    public function report(Request $request)
    {
        $branch = Branch::find(session('selected_branch_id'));
        if (!$branch) {
            return redirect()->route('admin.branch.select');
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfWeek()->format('Y-m-d'));
        $employeeId = $request->input('employee_id');

        $query = TimeLog::with(['employee.user'])
            ->whereHas('employee', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->whereBetween('date', [$startDate, $endDate]);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $timeLogs = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $employees = Employee::with('user')
            ->where('branch_id', $branch->id)
            ->get();

        // Calculate summary statistics
        $summary = [
            'total_hours' => 0,
            'total_breaks' => 0,
            'average_hours_per_day' => 0,
            'total_employees' => $timeLogs->unique('employee_id')->count(),
            'completed_shifts' => $timeLogs->whereNotNull('clock_out')->count(),
            'active_shifts' => $timeLogs->whereNull('clock_out')->count(),
        ];

        foreach ($timeLogs as $log) {
            if ($log->clock_in && $log->clock_out) {
                $hours = Carbon::parse($log->clock_in)->diffInHours(Carbon::parse($log->clock_out));
                $summary['total_hours'] += $hours;
            }
            if ($log->break_duration) {
                $summary['total_breaks'] += $log->break_duration;
            }
        }

        $daysCount = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $summary['average_hours_per_day'] = $daysCount > 0 ? round($summary['total_hours'] / $daysCount, 2) : 0;

        return view('admin.clock.report', compact('timeLogs', 'employees', 'summary', 'startDate', 'endDate', 'employeeId'));
    }
} 