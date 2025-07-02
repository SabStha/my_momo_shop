<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'credits_reward',
        'requirements',
        'validation_rules',
        'requires_badge',
        'required_badge_class_id',
        'is_active',
        'max_completions'
    ];

    protected $casts = [
        'credits_reward' => 'integer',
        'requirements' => 'array',
        'validation_rules' => 'array',
        'requires_badge' => 'boolean',
        'is_active' => 'boolean',
        'max_completions' => 'integer'
    ];

    public function requiredBadgeClass()
    {
        return $this->belongsTo(BadgeClass::class, 'required_badge_class_id');
    }

    public function completions()
    {
        return $this->hasMany(UserTaskCompletion::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOneTime($query)
    {
        return $query->where('type', 'one_time');
    }

    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }

    public function scopeRepeatable($query)
    {
        return $query->whereIn('type', ['daily', 'weekly', 'monthly']);
    }

    public function getTypeDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getRequirementsTextAttribute()
    {
        if (!$this->requirements) {
            return 'Complete the task to earn credits';
        }

        $text = [];
        foreach ($this->requirements as $requirement) {
            $text[] = $requirement['description'] ?? $requirement;
        }

        return implode(', ', $text);
    }

    public function getValidationRulesTextAttribute()
    {
        if (!$this->validation_rules) {
            return 'Automatic validation';
        }

        $text = [];
        foreach ($this->validation_rules as $rule) {
            $text[] = $rule['description'] ?? $rule;
        }

        return implode(', ', $text);
    }

    public function canBeCompletedByUser($user)
    {
        // Check if user has required badge
        if ($this->requires_badge && $this->required_badge_class_id) {
            $hasBadge = $user->userBadges()
                ->where('badge_class_id', $this->required_badge_class_id)
                ->where('status', 'active')
                ->exists();

            if (!$hasBadge) {
                return false;
            }
        }

        // Check max completions
        if ($this->max_completions) {
            $completionCount = $this->completions()
                ->where('user_id', $user->id)
                ->count();

            if ($completionCount >= $this->max_completions) {
                return false;
            }
        }

        // Check frequency limits
        if ($this->type === 'daily') {
            $todayCompletions = $this->completions()
                ->where('user_id', $user->id)
                ->whereDate('completed_at', today())
                ->count();

            if ($todayCompletions > 0) {
                return false;
            }
        } elseif ($this->type === 'weekly') {
            $weekCompletions = $this->completions()
                ->where('user_id', $user->id)
                ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            if ($weekCompletions > 0) {
                return false;
            }
        } elseif ($this->type === 'monthly') {
            $monthCompletions = $this->completions()
                ->where('user_id', $user->id)
                ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();

            if ($monthCompletions > 0) {
                return false;
            }
        }

        return true;
    }

    public function getNextAvailableDate($user)
    {
        if ($this->type === 'one_time') {
            return null; // One-time tasks don't repeat
        }

        $lastCompletion = $this->completions()
            ->where('user_id', $user->id)
            ->latest('completed_at')
            ->first();

        if (!$lastCompletion) {
            return now();
        }

        return match($this->type) {
            'daily' => $lastCompletion->completed_at->addDay(),
            'weekly' => $lastCompletion->completed_at->addWeek(),
            'monthly' => $lastCompletion->completed_at->addMonth(),
            default => null
        };
    }

    public function getTimeUntilAvailable($user)
    {
        $nextAvailable = $this->getNextAvailableDate($user);
        if (!$nextAvailable) {
            return null;
        }

        return $nextAvailable->diffForHumans();
    }

    public function getCompletionCountForUser($user)
    {
        return $this->completions()
            ->where('user_id', $user->id)
            ->count();
    }

    public function getTotalCreditsEarnedForUser($user)
    {
        return $this->completions()
            ->where('user_id', $user->id)
            ->sum('credits_earned');
    }
} 