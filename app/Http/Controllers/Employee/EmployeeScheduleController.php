<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:manager')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek());
        $endDate = Carbon::parse($startDate)->endOfWeek();
        $employeeId = $request->get('employee_id');

        $query = EmployeeSchedule::with('employee')
            ->whereBetween('work_date', [$startDate, $endDate]);

        // If not a manager, only show own schedule
        if (!Auth::user()->hasRole('manager')) {
            $query->where('employee_id', Auth::user()->employee_id);
        } elseif ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $schedules = $query->get()->groupBy('work_date');
        $employees = Employee::when(!Auth::user()->hasRole('manager'), function ($query) {
            $query->where('id', Auth::user()->employee_id);
        })->get();

        return view('employee-schedules.index', compact('schedules', 'employees', 'startDate'));
    }

    public function create()
    {
        $employees = Employee::all();
        return view('employee-schedules.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(EmployeeSchedule::rules());

        $schedule = new EmployeeSchedule($validated);

        if ($schedule->hasOverlappingShift()) {
            return back()->withErrors(['shift' => 'This shift overlaps with an existing shift.']);
        }

        $schedule->save();

        return redirect()->route('admin.employee-schedules.index')
            ->with('success', 'Schedule created successfully.');
    }

    public function show(EmployeeSchedule $employeeSchedule)
    {
        return view('employee-schedules.show', compact('employeeSchedule'));
    }

    public function edit(EmployeeSchedule $employeeSchedule)
    {
        $employees = Employee::all();
        return view('employee-schedules.edit', compact('employeeSchedule', 'employees'));
    }

    public function update(Request $request, EmployeeSchedule $employeeSchedule)
    {
        $validated = $request->validate(EmployeeSchedule::rules());

        $employeeSchedule->fill($validated);

        if ($employeeSchedule->hasOverlappingShift()) {
            return back()->withErrors(['shift' => 'This shift overlaps with an existing shift.']);
        }

        $employeeSchedule->save();

        return redirect()->route('admin.employee-schedules.index')
            ->with('success', 'Schedule updated successfully.');
    }

    public function destroy(EmployeeSchedule $employeeSchedule)
    {
        $employeeSchedule->delete();

        return redirect()->route('admin.employee-schedules.index')
            ->with('success', 'Schedule deleted successfully.');
    }

    public function export(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfWeek());
        $endDate = Carbon::parse($startDate)->endOfWeek();

        $schedules = EmployeeSchedule::with('employee')
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get()
            ->groupBy('employee_id');

        $pdf = \PDF::loadView('employee-schedules.export', compact('schedules', 'startDate', 'endDate'));
        
        return $pdf->download('employee-schedules.pdf');
    }
} 