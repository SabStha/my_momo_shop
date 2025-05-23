<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryOrderItem extends Model
{
    protected $fillable = [
        'inventory_order_id',
        'stock_item_id',
        'quantity',
        'unit_price',
        'subtotal'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function order()
    {
        return $this->belongsTo(InventoryOrder::class, 'inventory_order_id');
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }
} 