<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyStockCheck extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'quantity_checked',
        'checked_at',
        'notes'
    ];

    protected $casts = [
        'checked_at' => 'date',
        'quantity_checked' => 'decimal:2'
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 