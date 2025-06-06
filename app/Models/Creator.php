<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class Creator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'points',
        'bio'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($creator) {
            if (!$creator->code) {
                $creator->code = Str::random(8);
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
        return $this->hasMany(Referral::class, 'creator_id');
    }

    public function getRankAttribute()
    {
        return static::where('points', '>', $this->points)->count() + 1;
    }

    public function isTrending()
    {
        $previousRank = Cache::get('creator_rank_' . $this->id);
        $currentRank = $this->rank;
        
        if (!$previousRank) {
            Cache::put('creator_rank_' . $this->id, $currentRank, now()->addHours(24));
            return false;
        }

        $rankChange = $previousRank - $currentRank;
        Cache::put('creator_rank_' . $this->id, $currentRank, now()->addHours(24));
        
        return $rankChange >= 3;
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
} 