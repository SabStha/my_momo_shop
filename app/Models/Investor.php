<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Investor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'company_name',
        'company_registration',
        'investment_type', // 'individual', 'corporate', 'angel', 'venture_capital'
        'total_investment_amount',
        'investment_date',
        'status', // 'active', 'inactive', 'pending'
        'notes',
        'tax_id',
        'bank_details',
        'contact_person',
        'website',
        'social_media',
        'risk_profile', // 'conservative', 'moderate', 'aggressive'
        'investment_horizon', // 'short_term', 'medium_term', 'long_term'
        'preferred_communication',
        'is_verified',
        'verification_date',
        'user_id',
    ];

    protected $casts = [
        'total_investment_amount' => 'decimal:2',
        'investment_date' => 'datetime',
        'verification_date' => 'datetime',
        'is_verified' => 'boolean',
        'bank_details' => 'array',
        'social_media' => 'array'
    ];

    public function investments(): HasMany
    {
        return $this->hasMany(InvestorInvestment::class);
    }

    public function payouts(): HasMany
    {
        return $this->hasMany(InvestorPayout::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(InvestorReport::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalOwnershipPercentageAttribute()
    {
        return $this->investments()->sum('ownership_percentage');
    }

    public function getTotalInvestmentValueAttribute()
    {
        return $this->investments()->sum('investment_amount');
    }

    public function getActiveInvestmentsAttribute()
    {
        return $this->investments()->where('status', 'active')->get();
    }

    public function getBranchesAttribute()
    {
        return $this->investments()
            ->with('branch')
            ->get()
            ->pluck('branch')
            ->unique('id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('investment_type', $type);
    }

    public function getMonthlyPayoutAttribute()
    {
        // Calculate monthly payout based on ownership percentage and branch profits
        $totalPayout = 0;
        
        foreach ($this->investments as $investment) {
            if ($investment->status === 'active' && $investment->branch) {
                $branchRevenue = Order::where('branch_id', $investment->branch_id)
                    ->where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->sum('total_amount');
                
                $branchProfit = $branchRevenue * 0.3; // Assuming 30% profit margin
                $investorShare = $branchProfit * ($investment->ownership_percentage / 100);
                $totalPayout += $investorShare;
            }
        }
        
        return $totalPayout;
    }

    public function getTotalPayoutsAttribute()
    {
        return $this->payouts()->sum('amount');
    }

    public function getROIAttribute()
    {
        $totalInvested = $this->getTotalInvestmentValueAttribute();
        $totalPayouts = $this->getTotalPayoutsAttribute();
        
        if ($totalInvested > 0) {
            return (($totalPayouts - $totalInvested) / $totalInvested) * 100;
        }
        
        return 0;
    }
} 