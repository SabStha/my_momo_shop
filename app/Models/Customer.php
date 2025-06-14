<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'total_spent',
        'total_orders',
        'last_order_at',
        'customer_segment',
        'loyalty_points',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'total_spent' => 'decimal:2',
        'loyalty_points' => 'decimal:2',
        'is_active' => 'boolean',
        'last_order_at' => 'datetime'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function feedback()
    {
        return $this->hasMany(CustomerFeedback::class);
    }

    public function segment()
    {
        return $this->belongsTo(CustomerSegment::class, 'customer_segment', 'name');
    }

    public function getAverageOrderValueAttribute()
    {
        if ($this->total_orders === 0) return 0;
        return $this->total_spent / $this->total_orders;
    }

    public function getDaysSinceLastOrderAttribute()
    {
        if (!$this->last_order_at) return null;
        return now()->diffInDays($this->last_order_at);
    }

    public function getLoyaltyTierAttribute()
    {
        if ($this->total_spent >= 1000) return 'VIP';
        if ($this->total_spent >= 500) return 'Loyal';
        if ($this->total_spent >= 100) return 'Regular';
        return 'New';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeBySegment($query, $segment)
    {
        return $query->where('customer_segment', $segment);
    }

    public function scopeByLoyaltyTier($query, $tier)
    {
        return match($tier) {
            'VIP' => $query->where('total_spent', '>=', 1000),
            'Loyal' => $query->where('total_spent', '>=', 500)->where('total_spent', '<', 1000),
            'Regular' => $query->where('total_spent', '>=', 100)->where('total_spent', '<', 500),
            default => $query->where('total_spent', '<', 100)
        };
    }
} 