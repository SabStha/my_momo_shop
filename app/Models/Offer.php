<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'discount',
        'valid_from',
        'valid_until',
        'is_active',
        'code',
        'min_purchase',
        'max_discount',
        'type',
        'target_audience',
        'ai_generated',
        'ai_reasoning',
        'branch_id',
        'user_id'
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'discount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'ai_generated' => 'boolean'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('valid_from', '<=', now())
                    ->where('valid_until', '>=', now());
    }

    public function scopeAIGenerated($query)
    {
        return $query->where('ai_generated', true);
    }

    public function scopePersonalized($query)
    {
        return $query->where('target_audience', 'personalized');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhereNull('user_id');
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function claims()
    {
        return $this->hasMany(OfferClaim::class);
    }

    public function scopeClaimedBy($query, $userId)
    {
        return $query->whereHas('claims', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeNotClaimedBy($query, $userId)
    {
        return $query->whereDoesntHave('claims', function($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }
} 