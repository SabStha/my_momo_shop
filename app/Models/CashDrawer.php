<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashDrawer extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'total_cash'
    ];

    protected $casts = [
        'date' => 'date',
        'closed_at' => 'datetime'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(CashDrawerAdjustment::class);
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function getBalanceAttribute()
    {
        return $this->total_cash;
    }

    public function getTotalChangeAttribute()
    {
        $total = 0;
        foreach ($this->denominations as $denomination => $count) {
            $total += $denomination * $count;
        }
        return $total;
    }

    public function hasLowBalance()
    {
        return $this->total_cash < 1000; // Configurable minimum balance
    }

    public function hasHighBalance()
    {
        return $this->total_cash > 10000; // Configurable maximum balance
    }
} 