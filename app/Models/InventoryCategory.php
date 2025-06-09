<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCategory extends Model
{
    use HasFactory, BranchAware;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'branch_id'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'category_id');
    }
} 