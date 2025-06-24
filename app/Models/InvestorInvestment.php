<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorInvestment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'investor_id',
        'branch_id',
        'investment_amount',
        'ownership_percentage',
        'investment_date',
        'status', // 'active', 'inactive', 'pending', 'sold'
        'terms_conditions',
        'exit_strategy',
        'expected_return',
        'risk_level', // 'low', 'medium', 'high'
        'investment_type', // 'equity', 'debt', 'convertible_note'
        'maturity_date',
        'interest_rate',
        'payment_frequency', // 'monthly', 'quarterly', 'annually'
        'notes',
        'documents',
        'approved_by',
        'approval_date'
    ];

    protected $casts = [
        'investment_amount' => 'decimal:2',
        'ownership_percentage' => 'decimal:2',
        'investment_date' => 'datetime',
        'maturity_date' => 'datetime',
        'approval_date' => 'datetime',
        'expected_return' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'terms_conditions' => 'array',
        'documents' => 'array'
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payouts()
    {
        return $this->hasMany(InvestorPayout::class, 'investment_id');
    }

    public function getCurrentValueAttribute()
    {
        // Improved: Use branch orders relationship and fallback
        if ($this->branch && $this->status === 'active') {
            $orders = $this->branch->orders()->where('status', 'completed');
            if ($orders->count() > 0) {
                $revenue = $orders->sum('total_amount');
                $branchValue = $revenue * 3; // 3x revenue multiple
                return $branchValue * ($this->ownership_percentage / 100);
            }
        }
        // Fallback: original investment amount
        return $this->investment_amount;
    }

    public function getTotalPayoutsAttribute()
    {
        return $this->payouts()->sum('amount');
    }

    public function getROIAttribute()
    {
        $totalPayouts = $this->getTotalPayoutsAttribute();
        
        if ($this->investment_amount > 0) {
            return (($totalPayouts - $this->investment_amount) / $this->investment_amount) * 100;
        }
        
        return 0;
    }

    public function getMonthlyPayoutAttribute()
    {
        if ($this->branch && $this->status === 'active') {
            $branchRevenue = Order::where('branch_id', $this->branch_id)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount');
            
            $branchProfit = $branchRevenue * 0.3; // 30% profit margin
            return $branchProfit * ($this->ownership_percentage / 100);
        }
        
        return 0;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByInvestor($query, $investorId)
    {
        return $query->where('investor_id', $investorId);
    }

    public function getInvestmentAgeAttribute()
    {
        return $this->investment_date->diffInDays(now());
    }

    public function getDaysToMaturityAttribute()
    {
        if ($this->maturity_date) {
            return $this->maturity_date->diffInDays(now());
        }
        return null;
    }
} 