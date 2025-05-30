<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryOrder extends Model
{
    protected $fillable = [
        'supplier_name',
        'supplier_contact',
        'expected_delivery',
        'status',
        'notes',
        'total_amount',
        'completed_at',
        'cancelled_at'
    ];

    protected $casts = [
        'expected_delivery' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    public function items()
    {
        return $this->hasMany(InventoryOrderItem::class);
    }
} 