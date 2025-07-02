<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeClass extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'icon',
        'is_public',
        'is_active',
        'requirements',
        'benefits'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'requirements' => 'array',
        'benefits' => 'array'
    ];

    public function ranks()
    {
        return $this->hasMany(BadgeRank::class);
    }

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    public function badgeProgress()
    {
        return $this->hasMany(BadgeProgress::class);
    }

    public function creditTasks()
    {
        return $this->hasMany(CreditTask::class, 'required_badge_class_id');
    }

    public function creditRewards()
    {
        return $this->hasMany(CreditReward::class, 'required_badge_class_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function getDisplayNameAttribute()
    {
        return $this->icon . ' ' . $this->name;
    }

    public function getRequirementsTextAttribute()
    {
        if (!$this->requirements) {
            return 'No specific requirements';
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
            return 'Standard benefits';
        }

        $text = [];
        foreach ($this->benefits as $benefit) {
            $text[] = $benefit['description'] ?? $benefit;
        }

        return implode(', ', $text);
    }
} 