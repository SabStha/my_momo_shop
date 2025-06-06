<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_order_id',
        'inventory_item_id',
        'quantity',
        'unit_price',
        'total_price'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(InventoryOrder::class, 'inventory_order_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
} 