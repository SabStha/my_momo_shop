<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashDrawerAdjustment extends Model
{
    protected $fillable = [
        'cash_drawer_id',
        'user_id',
        'denomination',
        'amount',
        'reason',
        'type'
    ];

    public function cashDrawer(): BelongsTo
    {
        return $this->belongsTo(CashDrawer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 