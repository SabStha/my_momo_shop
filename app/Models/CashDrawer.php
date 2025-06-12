<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashDrawer extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'opening_balance',
        'current_balance',
        'closing_balance',
        'date',
        'opened_by',
        'closed_by',
        'closed_at'
    ];

    protected $casts = [
        'date' => 'date',
        'closed_at' => 'datetime'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
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