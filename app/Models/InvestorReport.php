<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'investor_id',
        'report_type', // 'monthly', 'quarterly', 'annual', 'performance', 'payout'
        'title',
        'content',
        'generated_at',
        'period_start',
        'period_end',
        'status', // 'draft', 'sent', 'viewed'
        'sent_at',
        'viewed_at',
        'sent_by',
        'file_path',
        'email_sent',
        'email_subject',
        'email_body',
        'metrics_data',
        'charts_data'
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'email_sent' => 'boolean',
        'metrics_data' => 'array',
        'charts_data' => 'array'
    ];

    public function investor(): BelongsTo
    {
        return $this->belongsTo(Investor::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function scopeByInvestor($query, $investorId)
    {
        return $query->where('investor_id', $investorId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('generated_at', [$startDate, $endDate]);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'bg-gray-100 text-gray-800',
            'sent' => 'bg-blue-100 text-blue-800',
            'viewed' => 'bg-green-100 text-green-800'
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-800';
    }

    public function getTypeBadgeAttribute()
    {
        $badges = [
            'monthly' => 'bg-purple-100 text-purple-800',
            'quarterly' => 'bg-blue-100 text-blue-800',
            'annual' => 'bg-green-100 text-green-800',
            'performance' => 'bg-orange-100 text-orange-800',
            'payout' => 'bg-yellow-100 text-yellow-800'
        ];

        return $badges[$this->report_type] ?? 'bg-gray-100 text-gray-800';
    }

    public function getFormattedGeneratedAtAttribute()
    {
        return $this->generated_at->format('M d, Y H:i');
    }

    public function getFormattedPeriodAttribute()
    {
        if ($this->period_start && $this->period_end) {
            return $this->period_start->format('M d, Y') . ' - ' . $this->period_end->format('M d, Y');
        }
        return 'N/A';
    }
} 