<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AmaCreditTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'ama_credit_id',
        'type',
        'amount',
        'description',
        'source',
        'metadata',
        'expires_at',
        'is_expired'
    ];

    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'is_expired' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function amaCredit()
    {
        return $this->belongsTo(AmaCredit::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeEarned($query)
    {
        return $query->where('type', 'earned');
    }

    public function scopeSpent($query)
    {
        return $query->where('type', 'spent');
    }

    public function scopeExpired($query)
    {
        return $query->where('type', 'expired');
    }

    public function scopeActive($query)
    {
        return $query->where('is_expired', false);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('type', 'earned')
            ->where('is_expired', false)
            ->where('expires_at', '<=', now()->addDays($days))
            ->where('expires_at', '>', now());
    }

    public function getDisplayAmountAttribute()
    {
        $prefix = $this->type === 'earned' ? '+' : '-';
        return $prefix . $this->amount . ' AmaKo Credits';
    }

    public function getTypeDisplayAttribute()
    {
        return ucfirst($this->type);
    }

    public function getStatusColorAttribute()
    {
        return match($this->type) {
            'earned' => 'success',
            'spent' => 'warning',
            'expired' => 'danger',
            default => 'secondary'
        };
    }

    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getTimeUntilExpiryAttribute()
    {
        if (!$this->expires_at || $this->is_expired) {
            return null;
        }

        return $this->expires_at->diffForHumans();
    }

    public function getExpiryDateAttribute()
    {
        return $this->expires_at ? $this->expires_at->format('M j, Y') : null;
    }

    public function getMetadataTextAttribute()
    {
        if (!$this->metadata) {
            return '';
        }

        $text = [];
        foreach ($this->metadata as $key => $value) {
            if (is_string($value)) {
                $text[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }

        return implode(', ', $text);
    }

    public function getSourceDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->source ?? 'manual'));
    }
} 