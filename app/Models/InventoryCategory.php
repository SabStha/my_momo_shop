<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'category_code', 'code');
    }
} 