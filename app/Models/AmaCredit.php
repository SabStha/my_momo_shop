<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class AmaCredit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'current_balance',
        'total_earned',
        'total_spent',
        'weekly_earned',
        'weekly_reset_date',
        'weekly_cap',
        'last_activity_at'
    ];

    protected $casts = [
        'current_balance' => 'integer',
        'total_earned' => 'integer',
        'total_spent' => 'integer',
        'weekly_earned' => 'integer',
        'weekly_cap' => 'integer',
        'weekly_reset_date' => 'date',
        'last_activity_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(AmaCreditTransaction::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('current_balance', '>', 0);
    }

    public function getWeeklyRemainingAttribute()
    {
        $this->checkWeeklyReset();
        return max(0, $this->weekly_cap - $this->weekly_earned);
    }

    public function getWeeklyProgressPercentageAttribute()
    {
        $this->checkWeeklyReset();
        if ($this->weekly_cap <= 0) {
            return 0;
        }
        return min(100, ($this->weekly_earned / $this->weekly_cap) * 100);
    }

    public function checkWeeklyReset()
    {
        if ($this->weekly_reset_date->isPast()) {
            $this->weekly_earned = 0;
            $this->weekly_reset_date = Carbon::now()->addWeek();
            $this->save();
        }
    }

    public function canEarnCredits($amount)
    {
        $this->checkWeeklyReset();
        return $this->weekly_earned + $amount <= $this->weekly_cap;
    }

    public function addCredits($amount, $description = '', $source = null, $metadata = [])
    {
        if (!$this->canEarnCredits($amount)) {
            throw new \Exception('Weekly credit cap would be exceeded');
        }

        $this->current_balance += $amount;
        $this->total_earned += $amount;
        $this->weekly_earned += $amount;
        $this->last_activity_at = now();
        $this->save();

        // Create transaction record
        $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'earned',
            'amount' => $amount,
            'description' => $description,
            'source' => $source,
            'metadata' => $metadata,
            'expires_at' => now()->addYear(), // Credits expire after 1 year
        ]);

        return $this;
    }

    public function spendCredits($amount, $description = '', $source = null, $metadata = [])
    {
        if ($this->current_balance < $amount) {
            throw new \Exception('Insufficient credits');
        }

        $this->current_balance -= $amount;
        $this->total_spent += $amount;
        $this->last_activity_at = now();
        $this->save();

        // Create transaction record
        $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'spent',
            'amount' => $amount,
            'description' => $description,
            'source' => $source,
            'metadata' => $metadata,
        ]);

        return $this;
    }

    public function getExpiredCredits()
    {
        return $this->transactions()
            ->where('type', 'earned')
            ->where('expires_at', '<', now())
            ->where('is_expired', false)
            ->get();
    }

    public function expireCredits()
    {
        $expiredTransactions = $this->getExpiredCredits();
        $totalExpired = 0;

        foreach ($expiredTransactions as $transaction) {
            $transaction->update(['is_expired' => true]);
            $totalExpired += $transaction->amount;
        }

        if ($totalExpired > 0) {
            $this->current_balance = max(0, $this->current_balance - $totalExpired);
            $this->save();

            // Create expiration transaction record
            $this->transactions()->create([
                'user_id' => $this->user_id,
                'type' => 'expired',
                'amount' => $totalExpired,
                'description' => 'Credits expired after 1 year',
                'source' => 'expiration',
                'is_expired' => true,
            ]);
        }

        return $totalExpired;
    }

    public function getDisplayBalanceAttribute()
    {
        return $this->current_balance . ' AmaKo Credits';
    }

    public function getWeeklyDisplayAttribute()
    {
        $this->checkWeeklyReset();
        return $this->weekly_earned . ' / ' . $this->weekly_cap . ' credits this week';
    }

    public function getNextResetDateAttribute()
    {
        return $this->weekly_reset_date->format('M j, Y');
    }

    public function getTimeUntilResetAttribute()
    {
        return $this->weekly_reset_date->diffForHumans();
    }
} 