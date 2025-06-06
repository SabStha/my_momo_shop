<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        $currentMonth = $request->get('month', Carbon::now()->month);
        $currentYear = $request->get('year', Carbon::now()->year);

        $workHours = $employee->getMonthlyWorkHours($currentMonth, $currentYear);
        $salary = $employee->getMonthlySalary($currentMonth, $currentYear);
        $isWorking = $employee->isCurrentlyWorking();
        $currentShift = $isWorking ? $employee->getCurrentShift() : null;

        // Get recent time logs
        $timeLogs = $employee->timeLogs()
            ->whereMonth('clock_in', $currentMonth)
            ->whereYear('clock_in', $currentYear)
            ->orderBy('clock_in', 'desc')
            ->paginate(10);

        return view('employee.dashboard', compact(
            'employee',
            'workHours',
            'salary',
            'isWorking',
            'currentShift',
            'timeLogs',
            'currentMonth',
            'currentYear'
        ));
    }
} 