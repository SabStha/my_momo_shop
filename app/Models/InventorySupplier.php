<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySupplier extends Model
{
    use BranchAware;

    protected $fillable = [
        'name',
        'code',
        'contact_person',
        'phone',
        'email',
        'address',
        'is_active',
        'branch_id'
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'supplier_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(InventoryOrder::class, 'supplier_id');
    }
} 