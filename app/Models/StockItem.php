<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    protected $fillable = [
        'name', 'category', 'quantity', 'unit', 'cost', 'expiry'
    ];
} 