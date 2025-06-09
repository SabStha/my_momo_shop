<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AdminClockController extends Controller
{
    public function index(Request $request)
    {
        try {
            $date = $request->get('date', now()->format('Y-m-d'));
            $timeLogs = TimeLog::with('employee.user')
                ->whereDate('clock_in', $date)
                ->orderBy('clock_in', 'desc')
                ->get();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'html' => view('admin.clock._time_logs', compact('timeLogs'))->render()
                ]);
            }

            // Get last 7 days for the date selector
            $lastSevenDays = collect(range(0, 6))->map(function($day) {
                return now()->subDays($day)->format('Y-m-d');
            });

            return view('admin.clock.index', compact('timeLogs', 'date', 'lastSevenDays'));
        } catch (\Exception $e) {
            Log::error('Error in clock index: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading clock records'
                ], 500);
            }
            return back()->with('error', 'Error loading clock records');
        }
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
        try {
            $term = $request->get('term');
            
            if (empty($term)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search term is required'
                ], 400);
            }

            // Log the search term for debugging
            \Log::info('Employee search term: ' . $term);

            $employees = Employee::with('user')
                ->where(function($query) use ($term) {
                    $query->whereHas('user', function($q) use ($term) {
                        $q->where('name', 'like', "%{$term}%")
                          ->orWhere('email', 'like', "%{$term}%");
                })
                    ->orWhere('employee_number', 'like', "%{$term}%");
                })
                ->where('status', 'active')
                ->limit(10)
                ->get();

            // Log the number of results for debugging
            \Log::info('Employee search results count: ' . $employees->count());

            $formattedEmployees = $employees->map(function($employee) {
                    return [
                        'value' => $employee->id,
                    'label' => sprintf(
                        '%s (ID: %s, Email: %s)',
                        $employee->user->name,
                        $employee->employee_number,
                        $employee->user->email
                    ),
                    'employee_id' => $employee->employee_number,
                    'email' => $employee->user->email
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $formattedEmployees
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in employee search: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching employees'
            ], 500);
        }
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

    public function generateReport(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'report_type' => 'required|in:daily,weekly,monthly',
                'date' => 'required|date'
            ]);

            $employee = Employee::findOrFail($request->employee_id);
            $date = Carbon::parse($request->date);
            
            $query = TimeLog::with('employee.user')->where('employee_id', $employee->id);
            
            switch ($request->report_type) {
                case 'weekly':
                    $startDate = $date->startOfWeek();
                    $endDate = $date->copy()->endOfWeek();
                    break;
                case 'monthly':
                    $startDate = $date->startOfMonth();
                    $endDate = $date->copy()->endOfMonth();
                    break;
                default: // daily
                    $startDate = $date->startOfDay();
                    $endDate = $date->copy()->endOfDay();
            }
            
            $timeLogs = $query->whereBetween('clock_in', [$startDate, $endDate])
                             ->orderBy('clock_in', 'desc')
                             ->get();

            if ($timeLogs->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'html' => view('admin.clock._report', compact('timeLogs'))->render()
                ]);
            }

            $html = view('admin.clock._report', compact('timeLogs'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'log_id' => 'required|exists:time_logs,id',
                'clock_in' => 'required|date',
                'clock_out' => 'nullable|date',
                'break_start' => 'nullable|date',
                'break_end' => 'nullable|date',
                'notes' => 'nullable|string'
            ]);

            $timeLog = TimeLog::findOrFail($request->log_id);
            
            // Update the time log
            $timeLog->update([
                'clock_in' => $request->clock_in,
                'clock_out' => $request->clock_out,
                'break_start' => $request->break_start,
                'break_end' => $request->break_end,
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Time log updated successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating time log: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating time log'
            ], 500);
        }
    }

    public function handleAction(Request $request)
    {
        try {
            $request->validate([
                'action' => 'required|string|in:clock_in,clock_out,start_break,end_break',
                'employee_id' => 'required|exists:employees,id',
                'date' => 'required|date'
            ]);

            $employee = Employee::findOrFail($request->employee_id);
            $date = Carbon::parse($request->date);

            switch ($request->action) {
                case 'clock_in':
                    if ($employee->isCurrentlyWorking()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Employee is already clocked in.'
                        ], 400);
                    }
                    $employee->timeLogs()->create([
                        'clock_in' => now(),
                        'status' => 'active'
                    ]);
                    break;

                case 'clock_out':
                    $currentShift = $employee->getCurrentShift();
                    if (!$currentShift) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Employee is not currently clocked in.'
                        ], 400);
                    }
                    if ($currentShift->status === 'on_break') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Employee is currently on break. Please end the break first.'
                        ], 400);
                    }
                    $currentShift->update([
                        'clock_out' => now(),
                        'status' => 'completed'
                    ]);
                    break;

                case 'start_break':
                    $currentShift = $employee->getCurrentShift();
                    if (!$currentShift) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Employee is not currently clocked in.'
                        ], 400);
                    }
                    if ($currentShift->status === 'on_break') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Employee is already on break.'
                        ], 400);
                    }
                    $currentShift->update([
                        'break_start' => now(),
                        'status' => 'on_break'
                    ]);
                    break;

                case 'end_break':
                    $currentShift = $employee->getCurrentShift();
                    if (!$currentShift) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Employee is not currently clocked in.'
                        ], 400);
                    }
                    if ($currentShift->status !== 'on_break') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Employee is not currently on break.'
                        ], 400);
                    }
                    $currentShift->update([
                        'break_end' => now(),
                        'status' => 'active'
                    ]);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Action completed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in clock action: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request'
            ], 500);
        }
    }
} 