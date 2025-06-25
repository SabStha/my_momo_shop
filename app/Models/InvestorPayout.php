<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorPayout extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'investor_id',
        'investment_id',
        'branch_id',
        'amount',
        'payout_date',
        'payout_type', // 'dividend', 'interest', 'principal', 'profit_share'
        'period_start',
        'period_end',
        'status', // 'pending', 'processed', 'paid', 'failed'
        'payment_method', // 'bank_transfer', 'check', 'cash', 'digital_wallet'
        'reference_number',
        'notes',
        'processed_by',
        'processed_at',
        'tax_amount',
        'net_amount',
        'currency',
        'exchange_rate'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payout_date' => 'datetime',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'processed_at' => 'datetime',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4'
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function investment(): BelongsTo
    {
        return $this->belongsTo(InvestorInvestment::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopeByInvestor($query, $investorId)
    {
        return $query->where('investor_id', $investorId);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('payout_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payout_date', [$startDate, $endDate]);
    }

    public function getFormattedAmountAttribute()
    {
        return 'Rs ' . number_format($this->amount, 2);
    }

    public function getFormattedNetAmountAttribute()
    {
        return 'Rs ' . number_format($this->net_amount, 2);
    }

    public function getFormattedTaxAmountAttribute()
    {
        return 'Rs ' . number_format($this->tax_amount, 2);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processed' => 'bg-blue-100 text-blue-800',
            'paid' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800'
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getTypeBadgeAttribute()
    {
        $badges = [
            'dividend' => 'bg-purple-100 text-purple-800',
            'interest' => 'bg-blue-100 text-blue-800',
            'principal' => 'bg-green-100 text-green-800',
            'profit_share' => 'bg-orange-100 text-orange-800'
        ];

        return $badges[$this->payout_type] ?? 'bg-gray-100 text-gray-800';
    }
} 