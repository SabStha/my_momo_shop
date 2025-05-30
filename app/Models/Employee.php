<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'position',
        'salary',
        'hire_date',
        'status',
        'phone',
        'address',
        'emergency_contact'
    ];

    protected $casts = [
        'hire_date' => 'date',
        'salary' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }

    public function getMonthlyWorkHours($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');

        return $this->timeLogs()
            ->whereYear('clock_in', $year)
            ->whereMonth('clock_in', $month)
            ->whereNotNull('clock_out')
            ->get()
            ->sum(function ($record) {
                return Carbon::parse($record->clock_in)
                    ->diffInHours(Carbon::parse($record->clock_out));
            });
    }

    public function getMonthlySalary($month = null, $year = null)
    {
        $hours = $this->getMonthlyWorkHours($month, $year);
        return $hours * $this->salary;
    }

    public function isCurrentlyWorking(): bool
    {
        $lastRecord = $this->timeLogs()
            ->latest('clock_in')
            ->first();

        if (!$lastRecord) {
            return false;
        }

        return !$lastRecord->clock_out;
    }

    public function getCurrentShift()
    {
        return $this->timeLogs()
            ->whereNull('clock_out')
            ->latest('clock_in')
            ->first();
    }
} 