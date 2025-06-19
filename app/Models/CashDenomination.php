<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashDenomination extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'value',
        'quantity',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'quantity' => 'integer',
        'is_active' => 'boolean'
    ];

    public function changes()
    {
        return $this->hasMany(CashDenominationChange::class);
    }

    public function getTotalValueAttribute()
    {
        return $this->value * $this->quantity;
    }
} 