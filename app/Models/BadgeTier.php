<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeTier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'badge_rank_id',
        'name',
        'level',
        'description',
        'requirements',
        'benefits',
        'points_required',
        'is_active'
    ];

    protected $casts = [
        'level' => 'integer',
        'points_required' => 'integer',
        'is_active' => 'boolean',
        'requirements' => 'array',
        'benefits' => 'array'
    ];

    public function badgeRank()
    {
        return $this->belongsTo(BadgeRank::class);
    }

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('level');
    }

    public function getDisplayNameAttribute()
    {
        return $this->badgeRank->name . ' ' . $this->name;
    }

    public function getFullDisplayNameAttribute()
    {
        return $this->badgeRank->badgeClass->icon . ' ' . $this->badgeRank->name . ' ' . $this->name;
    }

    public function getRequirementsTextAttribute()
    {
        if (!$this->requirements) {
            return 'Earn ' . $this->points_required . ' points';
        }

        $text = [];
        foreach ($this->requirements as $requirement) {
            $text[] = $requirement['description'] ?? $requirement;
        }

        return implode(', ', $text);
    }

    public function getBenefitsTextAttribute()
    {
        if (!$this->benefits) {
            return 'Standard tier benefits';
        }

        $text = [];
        foreach ($this->benefits as $benefit) {
            $text[] = $benefit['description'] ?? $benefit;
        }

        return implode(', ', $text);
    }

    public function getNextTier()
    {
        return $this->badgeRank->tiers()
            ->where('level', '>', $this->level)
            ->orderBy('level')
            ->first();
    }

    public function getPreviousTier()
    {
        return $this->badgeRank->tiers()
            ->where('level', '<', $this->level)
            ->orderBy('level', 'desc')
            ->first();
    }

    public function isHighestTier()
    {
        return $this->level === $this->badgeRank->tiers()->max('level');
    }

    public function isLowestTier()
    {
        return $this->level === $this->badgeRank->tiers()->min('level');
    }
} 