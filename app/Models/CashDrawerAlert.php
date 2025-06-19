<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashDrawerAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'denomination',
        'low_threshold',
        'high_threshold',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the branch that owns the alert.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Check if current denomination count triggers a low alert
     */
    public function isLowAlert($currentCount)
    {
        return $this->is_active && $currentCount <= $this->low_threshold;
    }

    /**
     * Check if current denomination count triggers a high alert
     */
    public function isHighAlert($currentCount)
    {
        return $this->is_active && $currentCount >= $this->high_threshold;
    }

    /**
     * Get alert status for a given count
     */
    public function getAlertStatus($currentCount)
    {
        if ($this->isLowAlert($currentCount)) {
            return 'low';
        } elseif ($this->isHighAlert($currentCount)) {
            return 'high';
        }
        return 'normal';
    }
}
