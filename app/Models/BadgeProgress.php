<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeProgress extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'badge_class_id',
        'current_points',
        'total_points_earned',
        'progress_data',
        'last_activity_at'
    ];

    protected $casts = [
        'current_points' => 'integer',
        'total_points_earned' => 'integer',
        'progress_data' => 'array',
        'last_activity_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badgeClass()
    {
        return $this->belongsTo(BadgeClass::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForBadgeClass($query, $badgeClassId)
    {
        return $query->where('badge_class_id', $badgeClassId);
    }

    public function scopeActive($query)
    {
        return $query->where('current_points', '>', 0);
    }

    public function getProgressPercentageAttribute()
    {
        $nextTier = $this->getNextTier();
        if (!$nextTier) {
            return 100; // Max tier reached
        }

        $currentTier = $this->getCurrentTier();
        if (!$currentTier) {
            return 0; // No tier yet
        }

        $pointsForCurrentTier = $currentTier->points_required;
        $pointsForNextTier = $nextTier->points_required;
        $pointsNeeded = $pointsForNextTier - $pointsForCurrentTier;
        $pointsProgress = $this->current_points - $pointsForCurrentTier;

        if ($pointsNeeded <= 0) {
            return 100;
        }

        return min(100, max(0, ($pointsProgress / $pointsNeeded) * 100));
    }

    public function getCurrentTier()
    {
        $tiers = $this->badgeClass->ranks()
            ->with('tiers')
            ->get()
            ->flatMap(function ($rank) {
                return $rank->tiers;
            })
            ->sortBy('level');

        foreach ($tiers as $tier) {
            if ($this->current_points >= $tier->points_required) {
                continue;
            }
            return $tier->getPreviousTier();
        }

        return $tiers->last();
    }

    public function getNextTier()
    {
        $currentTier = $this->getCurrentTier();
        if (!$currentTier) {
            return $this->badgeClass->ranks()
                ->with('tiers')
                ->get()
                ->flatMap(function ($rank) {
                    return $rank->tiers;
                })
                ->sortBy('level')
                ->first();
        }

        return $currentTier->getNextTier();
    }

    public function getPointsToNextTierAttribute()
    {
        $nextTier = $this->getNextTier();
        if (!$nextTier) {
            return 0; // Max tier reached
        }

        return max(0, $nextTier->points_required - $this->current_points);
    }

    public function getCurrentRank()
    {
        $currentTier = $this->getCurrentTier();
        return $currentTier ? $currentTier->badgeRank : null;
    }

    public function getNextRank()
    {
        $currentRank = $this->getCurrentRank();
        if (!$currentRank) {
            return $this->badgeClass->ranks()->orderBy('level')->first();
        }

        return $currentRank->getNextRank();
    }

    public function addPoints($points, $source = null, $metadata = [])
    {
        $this->current_points += $points;
        $this->total_points_earned += $points;
        $this->last_activity_at = now();

        // Update progress data
        $progressData = $this->progress_data ?? [];
        $progressData['last_points_earned'] = $points;
        $progressData['last_source'] = $source;
        $progressData['last_earned_at'] = now()->toISOString();
        
        if ($metadata) {
            $progressData['last_metadata'] = $metadata;
        }

        $this->progress_data = $progressData;
        $this->save();

        return $this;
    }

    public function getProgressDataTextAttribute()
    {
        if (!$this->progress_data) {
            return 'No progress data available';
        }

        $text = [];
        if (isset($this->progress_data['last_source'])) {
            $text[] = 'Last activity: ' . $this->progress_data['last_source'];
        }
        if (isset($this->progress_data['last_points_earned'])) {
            $text[] = 'Points earned: ' . $this->progress_data['last_points_earned'];
        }

        return implode(', ', $text);
    }
} 