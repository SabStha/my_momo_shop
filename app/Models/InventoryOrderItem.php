<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryOrderItem extends Model
{
    use HasFactory, BranchAware;

    protected $fillable = [
        'inventory_order_id',
        'inventory_item_id',
        'quantity',
        'original_quantity',
        'unit_price',
        'total_price',
        'branch_id'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'original_quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(InventoryOrder::class, 'inventory_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
} 