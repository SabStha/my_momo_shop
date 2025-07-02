<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditReward extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'credits_cost',
        'type',
        'reward_data',
        'requires_badge',
        'required_badge_class_id',
        'is_active',
        'stock_quantity',
        'redeemed_count',
        'available_from',
        'available_until'
    ];

    protected $casts = [
        'credits_cost' => 'integer',
        'reward_data' => 'array',
        'requires_badge' => 'boolean',
        'is_active' => 'boolean',
        'stock_quantity' => 'integer',
        'redeemed_count' => 'integer',
        'available_from' => 'datetime',
        'available_until' => 'datetime'
    ];

    public function requiredBadgeClass()
    {
        return $this->belongsTo(BadgeClass::class, 'required_badge_class_id');
    }

    public function redemptions()
    {
        return $this->hasMany(UserRewardRedemption::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('available_from')
                    ->orWhere('available_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('available_until')
                    ->orWhere('available_until', '>=', now());
            });
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('stock_quantity')
                ->orWhereRaw('stock_quantity > redeemed_count');
        });
    }

    public function scopeFreeItem($query)
    {
        return $query->where('type', 'free_item');
    }

    public function scopeDiscount($query)
    {
        return $query->where('type', 'discount');
    }

    public function scopePrivilege($query)
    {
        return $query->where('type', 'privilege');
    }

    public function scopePhysical($query)
    {
        return $query->where('type', 'physical');
    }

    public function getTypeDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getRewardDataTextAttribute()
    {
        if (!$this->reward_data) {
            return 'Standard reward';
        }

        $text = [];
        foreach ($this->reward_data as $key => $value) {
            if (is_string($value)) {
                $text[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . $value;
            }
        }

        return implode(', ', $text);
    }

    public function getDisplayCostAttribute()
    {
        return $this->credits_cost . ' AmaKo Credits';
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock_quantity === null) {
            return 'unlimited';
        }

        $remaining = $this->stock_quantity - $this->redeemed_count;
        if ($remaining <= 0) {
            return 'out_of_stock';
        }

        if ($remaining <= 5) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockStatusColorAttribute()
    {
        return match($this->stock_status) {
            'unlimited' => 'success',
            'in_stock' => 'success',
            'low_stock' => 'warning',
            'out_of_stock' => 'danger',
            default => 'secondary'
        };
    }

    public function getStockStatusTextAttribute()
    {
        return match($this->stock_status) {
            'unlimited' => 'Unlimited',
            'in_stock' => 'In Stock',
            'low_stock' => 'Low Stock',
            'out_of_stock' => 'Out of Stock',
            default => 'Unknown'
        };
    }

    public function getRemainingStockAttribute()
    {
        if ($this->stock_quantity === null) {
            return null; // Unlimited
        }

        return max(0, $this->stock_quantity - $this->redeemed_count);
    }

    public function isAvailable()
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->available_from && $this->available_from->isFuture()) {
            return false;
        }

        if ($this->available_until && $this->available_until->isPast()) {
            return false;
        }

        if ($this->stock_status === 'out_of_stock') {
            return false;
        }

        return true;
    }

    public function canBeRedeemedByUser($user)
    {
        if (!$this->isAvailable()) {
            return false;
        }

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

        // Check if user has enough credits
        $amaCredit = $user->amaCredit;
        if (!$amaCredit || $amaCredit->current_balance < $this->credits_cost) {
            return false;
        }

        return true;
    }

    public function getAvailabilityTextAttribute()
    {
        if ($this->available_from && $this->available_from->isFuture()) {
            return 'Available from ' . $this->available_from->format('M j, Y');
        }

        if ($this->available_until && $this->available_until->isPast()) {
            return 'No longer available';
        }

        if ($this->available_until) {
            return 'Available until ' . $this->available_until->format('M j, Y');
        }

        return 'Available now';
    }

    public function getTimeUntilAvailableAttribute()
    {
        if ($this->available_from && $this->available_from->isFuture()) {
            return $this->available_from->diffForHumans();
        }

        return null;
    }

    public function getTimeUntilExpiryAttribute()
    {
        if ($this->available_until && $this->available_until->isFuture()) {
            return $this->available_until->diffForHumans();
        }

        return null;
    }
} 