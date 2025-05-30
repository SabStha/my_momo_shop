<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'status',
        'notes'
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTotalWorkHours()
    {
        if (!$this->clock_out) {
            return 0;
        }

        $totalMinutes = $this->clock_out->diffInMinutes($this->clock_in);
        
        // Subtract break time if exists
        if ($this->break_start && $this->break_end) {
            $breakMinutes = $this->break_end->diffInMinutes($this->break_start);
            $totalMinutes -= $breakMinutes;
        }

        return round($totalMinutes / 60, 2);
    }

    public function getBreakHours()
    {
        if (!$this->break_start || !$this->break_end) {
            return 0;
        }

        $breakMinutes = $this->break_end->diffInMinutes($this->break_start);
        return round($breakMinutes / 60, 2);
    }

    public static function getWeeklyReport($employeeId = null, $startDate = null, $endDate = null)
    {
        $query = self::with('employee.user');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('clock_in', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        } else {
            // Default to last 7 days
            $query->whereBetween('clock_in', [
                now()->subDays(7)->startOfDay(),
                now()->endOfDay()
            ]);
        }

        return $query->get()->groupBy(function($log) {
            return $log->clock_in->format('Y-m-d');
        });
    }

    public static function getMonthlyReport($employeeId = null, $year = null, $month = null)
    {
        $query = self::with('employee.user');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $date = Carbon::create($year ?? now()->year, $month ?? now()->month, 1);
        
        return $query->whereBetween('clock_in', [
            $date->startOfMonth(),
            $date->endOfMonth()
        ])->get()->groupBy(function($log) {
            return $log->clock_in->format('Y-m-d');
        });
    }

    public function calculateSalary($hourlyRate)
    {
        return $this->getTotalWorkHours() * $hourlyRate;
    }
} 