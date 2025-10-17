<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpactStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'period_start',
        'period_end',
        'period_type',
        'total_sales',
        'donation_amount',
        'donation_percentage',
        'plates_funded',
        'dogs_saved',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_sales' => 'decimal:2',
        'donation_amount' => 'decimal:2',
        'donation_percentage' => 'decimal:2',
        'plates_funded' => 'integer',
        'dogs_saved' => 'integer',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Scopes
    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeCompanyWide($query)
    {
        return $query->whereNull('branch_id');
    }

    public function scopeByPeriodType($query, $type)
    {
        return $query->where('period_type', $type);
    }

    public function scopeInPeriod($query, $start, $end)
    {
        return $query->where(function ($q) use ($start, $end) {
            $q->whereBetween('period_start', [$start, $end])
              ->orWhereBetween('period_end', [$start, $end])
              ->orWhere(function ($q2) use ($start, $end) {
                  $q2->where('period_start', '<=', $start)
                     ->where('period_end', '>=', $end);
              });
        });
    }

    public function scopeLatest($query, $periodType = 'monthly')
    {
        return $query->where('period_type', $periodType)
                     ->orderBy('period_end', 'desc');
    }

    // Helper methods
    public static function getCurrentMonthStats($branchId = null)
    {
        $query = self::byPeriodType('monthly')
                     ->where('period_start', '<=', now())
                     ->where('period_end', '>=', now());

        if ($branchId) {
            $query->forBranch($branchId);
        } else {
            $query->companyWide();
        }

        return $query->first();
    }

    public static function getTotalImpact($branchId = null)
    {
        $query = self::query();

        if ($branchId) {
            $query->forBranch($branchId);
        }

        return [
            'total_donation' => $query->sum('donation_amount'),
            'total_plates' => $query->sum('plates_funded'),
            'total_dogs' => $query->sum('dogs_saved'),
        ];
    }
}
