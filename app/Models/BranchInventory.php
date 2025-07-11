<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchInventory extends Model
{
    protected $table = 'branch_inventory';

    protected $fillable = [
        'branch_id',
        'inventory_item_id',
        'current_stock',
        'minimum_stock',
        'reorder_point',
        'is_main'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }
} 