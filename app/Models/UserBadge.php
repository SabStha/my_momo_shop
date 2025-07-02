<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBadge extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'badge_tier_id',
        'badge_rank_id',
        'badge_class_id',
        'status',
        'earned_at',
        'expires_at',
        'earned_data'
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'expires_at' => 'datetime',
        'earned_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function badgeTier()
    {
        return $this->belongsTo(BadgeTier::class);
    }

    public function badgeRank()
    {
        return $this->belongsTo(BadgeRank::class);
    }

    public function badgeClass()
    {
        return $this->belongsTo(BadgeClass::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForBadgeClass($query, $badgeClassId)
    {
        return $query->where('badge_class_id', $badgeClassId);
    }

    public function getDisplayNameAttribute()
    {
        return $this->badgeTier->full_display_name;
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'warning',
            'expired' => 'danger',
            default => 'secondary'
        };
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function getTimeUntilExpiryAttribute()
    {
        if (!$this->expires_at) {
            return null;
        }

        return $this->expires_at->diffForHumans();
    }

    public function getEarnedTimeAgoAttribute()
    {
        return $this->earned_at->diffForHumans();
    }

    public function getEarnedDataTextAttribute()
    {
        if (!$this->earned_data) {
            return 'Earned through normal progression';
        }

        $text = [];
        foreach ($this->earned_data as $key => $value) {
            if (is_string($value)) {
                $text[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }

        return implode(', ', $text);
    }
} 