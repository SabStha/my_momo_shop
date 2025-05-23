<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryCount extends Model
{
    protected $fillable = [
        'stock_item_id', 'count_date', 'actual_quantity', 'expected_quantity', 'difference', 'note'
    ];

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }
} 