<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserTaskCompletion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'credit_task_id',
        'credits_earned',
        'completion_data',
        'completed_at'
    ];

    protected $casts = [
        'credits_earned' => 'integer',
        'completion_data' => 'array',
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditTask()
    {
        return $this->belongsTo(CreditTask::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTask($query, $taskId)
    {
        return $query->where('credit_task_id', $taskId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('completed_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    public function getCompletionDataTextAttribute()
    {
        if (!$this->completion_data) {
            return 'Task completed successfully';
        }

        $text = [];
        foreach ($this->completion_data as $key => $value) {
            if (is_string($value)) {
                $text[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }

        return implode(', ', $text);
    }

    public function getCompletedTimeAgoAttribute()
    {
        return $this->completed_at->diffForHumans();
    }

    public function getDisplayCreditsAttribute()
    {
        return '+' . $this->credits_earned . ' AmaKo Credits';
    }
} 