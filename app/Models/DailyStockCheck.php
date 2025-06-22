<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyStockCheck extends Model
{
    protected $fillable = [
        'branch_id',
        'inventory_item_id',
        'check_date',
        'opening_stock',
        'closing_stock',
        'wastage',
        'notes',
        'checked_by'
    ];

    protected $casts = [
        'check_date' => 'date',
        'opening_stock' => 'decimal:2',
        'closing_stock' => 'decimal:2',
        'wastage' => 'decimal:2'
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
} 