<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class Creator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'points',
        'referral_count',
        'earnings',
        'status'
    ];

    protected $casts = [
        'points' => 'integer',
        'referral_count' => 'integer',
        'earnings' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($creator) {
            if (!$creator->code) {
                $creator->code = Str::random(8);
                Log::info('Generated new referral code', [
                    'creator_id' => $creator->id,
                    'referral_code' => $creator->code
                ]);
            }
            if (!Schema::hasColumn('creators', 'bio')) {
                // Skip bio field if column doesn't exist
                unset($creator->bio);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'creator_id', 'user_id');
    }

    public function getRankAttribute()
    {
        return static::where('points', '>', $this->points)->count() + 1;
    }

    public function isTrending()
    {
        return $this->referral_count > 0 && $this->referral_count % 5 === 0;
    }

    public function getDiscountAttribute()
    {
        return match(true) {
            $this->rank === 1 => 50,
            $this->rank <= 3 => 40,
            $this->rank <= 10 => 30,
            default => 20
        };
    }

    public function addPoints($points, $reason = '')
    {
        $oldPoints = $this->points;
        $this->points += $points;
        $this->save();

        Log::info('Creator points updated', [
            'creator_id' => $this->id,
            'user_id' => $this->user_id,
            'old_points' => $oldPoints,
            'points_added' => $points,
            'new_points' => $this->points,
            'reason' => $reason
        ]);

        return $this;
    }

    public function addEarnings($amount, $reason = '')
    {
        $oldEarnings = $this->earnings;
        $this->earnings += $amount;
        $this->save();

        Log::info('Creator earnings updated', [
            'creator_id' => $this->id,
            'user_id' => $this->user_id,
            'old_earnings' => $oldEarnings,
            'amount_added' => $amount,
            'new_earnings' => $this->earnings,
            'reason' => $reason
        ]);

        return $this;
    }
} 