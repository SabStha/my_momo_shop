<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserRewardRedemption extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'credit_reward_id',
        'credits_spent',
        'status',
        'redemption_data',
        'redeemed_at',
        'expires_at',
        'used_at',
        'notes'
    ];

    protected $casts = [
        'credits_spent' => 'integer',
        'redemption_data' => 'array',
        'redeemed_at' => 'datetime',
        'expires_at' => 'datetime',
        'used_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creditReward()
    {
        return $this->belongsTo(CreditReward::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForReward($query, $rewardId)
    {
        return $query->where('credit_reward_id', $rewardId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now());
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'active' => 'success',
            'used' => 'info',
            'expired' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    public function getStatusDisplayAttribute()
    {
        return ucfirst($this->status);
    }

    public function getDisplayCreditsAttribute()
    {
        return '-' . $this->credits_spent . ' AmaKo Credits';
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isActive()
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function canBeUsed()
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

    public function getExpiryDateAttribute()
    {
        return $this->expires_at ? $this->expires_at->format('M j, Y') : null;
    }

    public function getRedeemedTimeAgoAttribute()
    {
        return $this->redeemed_at->diffForHumans();
    }

    public function getUsedTimeAgoAttribute()
    {
        return $this->used_at ? $this->used_at->diffForHumans() : null;
    }

    public function getRedemptionDataTextAttribute()
    {
        if (!$this->redemption_data) {
            return 'Standard redemption';
        }

        $text = [];
        foreach ($this->redemption_data as $key => $value) {
            if (is_string($value)) {
                $text[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }

        return implode(', ', $text);
    }

    public function markAsUsed($notes = null)
    {
        $this->status = 'used';
        $this->used_at = now();
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();

        return $this;
    }

    public function markAsExpired()
    {
        $this->status = 'expired';
        $this->save();

        return $this;
    }

    public function cancel($notes = null)
    {
        $this->status = 'cancelled';
        if ($notes) {
            $this->notes = $notes;
        }
        $this->save();

        return $this;
    }
} 