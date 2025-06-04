<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'active',
        'expires_at',
        'min_order_amount',
        'max_uses',
        'used_count'
    ];

    protected $casts = [
        'active' => 'boolean',
        'expires_at' => 'datetime',
        'min_order_amount' => 'decimal:2',
        'value' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer'
    ];

    public function isValid()
    {
        if (!$this->active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function incrementUsage()
    {
        $this->increment('used_count');
    }

    public function userCoupons()
    {
        return $this->hasMany(UserCoupon::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_coupons')->withTimestamps()->withPivot('used_at');
    }
} 