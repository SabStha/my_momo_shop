<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        
        // Get salary history for the last 12 months
        $salaryHistory = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::now()->subMonths($i);
            $month = $date->month;
            $year = $date->year;
            
            $salaryHistory->push([
                'month' => $date->format('F Y'),
                'hours' => $employee->getMonthlyWorkHours($month, $year),
                'amount' => $employee->getMonthlySalary($month, $year),
                'month_number' => $month,
                'year' => $year,
            ]);
        }

        // Get detailed breakdown for current or selected month
        $selectedMonth = $request->get('month', Carbon::now()->month);
        $selectedYear = $request->get('year', Carbon::now()->year);

        $timeLogs = $employee->timeLogs()
            ->whereMonth('clock_in', $selectedMonth)
            ->whereYear('clock_in', $selectedYear)
            ->orderBy('clock_in', 'desc')
            ->get();

        $monthlyBreakdown = [
            'total_hours' => $employee->getMonthlyWorkHours($selectedMonth, $selectedYear),
            'hourly_rate' => $employee->hourly_rate,
            'total_salary' => $employee->getMonthlySalary($selectedMonth, $selectedYear),
            'time_logs' => $timeLogs,
        ];

        return view('employee.salary.index', compact(
            'employee',
            'salaryHistory',
            'monthlyBreakdown',
            'selectedMonth',
            'selectedYear'
        ));
    }

    public function downloadPayslip(Request $request)
    {
        $employee = $request->user()->employee;
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $data = [
            'employee' => $employee,
            'month' => Carbon::createFromDate($year, $month, 1)->format('F Y'),
            'hours' => $employee->getMonthlyWorkHours($month, $year),
            'hourly_rate' => $employee->hourly_rate,
            'total_salary' => $employee->getMonthlySalary($month, $year),
            'generated_at' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        return view('employee.salary.payslip', $data);
    }
} 