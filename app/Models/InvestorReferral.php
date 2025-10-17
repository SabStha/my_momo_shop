<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class InvestorReferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_investor_id',
        'referred_investor_id',
        'referral_code',
        'referred_email',
        'referred_name',
        'status',
        'investment_amount',
        'referral_bonus',
        'referral_percentage',
        'contacted_at',
        'invested_at',
        'notes',
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'referral_bonus' => 'decimal:2',
        'referral_percentage' => 'decimal:2',
        'contacted_at' => 'datetime',
        'invested_at' => 'datetime',
    ];

    // Relationships
    public function referrer()
    {
        return $this->belongsTo(Investor::class, 'referrer_investor_id');
    }

    public function referredInvestor()
    {
        return $this->belongsTo(Investor::class, 'referred_investor_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', 'invested');
    }

    public function scopeForReferrer($query, $investorId)
    {
        return $query->where('referrer_investor_id', $investorId);
    }

    // Helper methods
    public static function generateUniqueCode($investorId)
    {
        do {
            $code = 'INV-' . strtoupper(Str::random(8));
        } while (static::where('referral_code', $code)->exists());

        return $code;
    }

    public function markAsContacted()
    {
        $this->update([
            'status' => 'contacted',
            'contacted_at' => now(),
        ]);
    }

    public function markAsInvested($investorId, $amount)
    {
        $bonus = ($amount * $this->referral_percentage) / 100;

        $this->update([
            'status' => 'invested',
            'referred_investor_id' => $investorId,
            'investment_amount' => $amount,
            'referral_bonus' => $bonus,
            'invested_at' => now(),
        ]);

        return $bonus;
    }

    public function markAsDeclined()
    {
        $this->update(['status' => 'declined']);
    }

    public static function getReferralStats($investorId)
    {
        $referrals = self::forReferrer($investorId)->get();

        return [
            'total_referrals' => $referrals->count(),
            'successful_referrals' => $referrals->where('status', 'invested')->count(),
            'pending_referrals' => $referrals->whereIn('status', ['pending', 'contacted'])->count(),
            'total_earnings' => $referrals->where('status', 'invested')->sum('referral_bonus'),
            'total_investment_referred' => $referrals->where('status', 'invested')->sum('investment_amount'),
        ];
    }
}
