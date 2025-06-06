<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'work_date',
        'shift_start',
        'shift_end',
        'notes'
    ];

    protected $casts = [
        'work_date' => 'date',
        'shift_start' => 'datetime',
        'shift_end' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public static function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'work_date' => 'required|date',
            'shift_start' => 'required|date_format:H:i',
            'shift_end' => 'required|date_format:H:i|after:shift_start',
            'notes' => 'nullable|string|max:500'
        ];
    }

    public function hasOverlappingShift(): bool
    {
        return static::where('employee_id', $this->employee_id)
            ->where('work_date', $this->work_date)
            ->where(function ($query) {
                $query->whereBetween('shift_start', [$this->shift_start, $this->shift_end])
                    ->orWhereBetween('shift_end', [$this->shift_start, $this->shift_end]);
            })
            ->when($this->exists, function ($query) {
                $query->where('id', '!=', $this->id);
            })
            ->exists();
    }

    public function getDurationInHours(): float
    {
        $start = \Carbon\Carbon::parse($this->shift_start);
        $end = \Carbon\Carbon::parse($this->shift_end);
        return $end->diffInMinutes($start) / 60;
    }
} 