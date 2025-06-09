<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_order_id',
        'inventory_item_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function supplyOrder()
    {
        return $this->belongsTo(SupplyOrder::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
} 