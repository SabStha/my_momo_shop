<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashDenominationChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_denomination_id',
        'user_id',
        'previous_quantity',
        'new_quantity',
        'change_type',
        'reason'
    ];

    protected $casts = [
        'previous_quantity' => 'integer',
        'new_quantity' => 'integer'
    ];

    public function cashDenomination()
    {
        return $this->belongsTo(CashDenomination::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getQuantityDifferenceAttribute()
    {
        return $this->new_quantity - $this->previous_quantity;
    }

    public function getValueDifferenceAttribute()
    {
        return $this->cashDenomination->value * $this->quantity_difference;
    }
} 