<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'amount', 'usage_limit', 'user_limit', 'valid_from', 'valid_until', 'campaign_name', 'shop_only'
    ];

    public function userCoupons()
    {
        return $this->hasMany(UserCoupon::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_coupons')->withTimestamps()->withPivot('used_at');
    }
} 