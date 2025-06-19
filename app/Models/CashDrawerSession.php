<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashDrawerSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'opening_balance',
        'closing_balance',
        'opening_denominations',
        'closing_denominations',
        'opened_at',
        'closed_at',
        'notes',
        'discrepancy',
        'session_duration'
    ];

    protected $casts = [
        'opening_denominations' => 'array',
        'closing_denominations' => 'array',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'discrepancy' => 'decimal:2',
        'session_duration' => 'integer'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isOpen(): bool
    {
        return is_null($this->closed_at);
    }
}
