<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeRank extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'badge_class_id',
        'name',
        'code',
        'level',
        'description',
        'color',
        'requirements',
        'benefits',
        'is_active'
    ];

    protected $casts = [
        'level' => 'integer',
        'is_active' => 'boolean',
        'requirements' => 'array',
        'benefits' => 'array'
    ];

    public function badgeClass()
    {
        return $this->belongsTo(BadgeClass::class);
    }

    public function tiers()
    {
        return $this->hasMany(BadgeTier::class);
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
        return $this->name . ' ' . $this->badgeClass->name;
    }

    public function getFullDisplayNameAttribute()
    {
        return $this->badgeClass->icon . ' ' . $this->name . ' ' . $this->badgeClass->name;
    }

    public function getRequirementsTextAttribute()
    {
        if (!$this->requirements) {
            return 'Complete previous rank requirements';
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
            return 'Standard rank benefits';
        }

        $text = [];
        foreach ($this->benefits as $benefit) {
            $text[] = $benefit['description'] ?? $benefit;
        }

        return implode(', ', $text);
    }

    public function getNextRank()
    {
        return $this->badgeClass->ranks()
            ->where('level', '>', $this->level)
            ->orderBy('level')
            ->first();
    }

    public function getPreviousRank()
    {
        return $this->badgeClass->ranks()
            ->where('level', '<', $this->level)
            ->orderBy('level', 'desc')
            ->first();
    }
} 