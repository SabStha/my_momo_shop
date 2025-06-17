<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClockController extends Controller
{
    public function index()
    {
        $branch = session('selected_branch');
        if (!$branch) {
            return redirect()->route('admin.branch.select');
        }

        $timeLogs = TimeLog::with(['employee.user'])
            ->whereHas('employee', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })
            ->whereDate('date', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.clock.index', compact('timeLogs'));
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id'
        ]);

        $employee = Employee::findOrFail($request->employee_id);
        $branch = session('selected_branch');
        
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
        $branch = session('selected_branch');
        if (!$branch) {
            return response()->json(['error' => 'No branch selected'], 400);
        }

        $query = $request->input('query');
        $employees = Employee::with('user')
            ->where('branch_id', $branch->id)
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
        $branch = session('selected_branch');
        if (!$branch) {
            return response()->json(['error' => 'No branch selected'], 400);
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
} 