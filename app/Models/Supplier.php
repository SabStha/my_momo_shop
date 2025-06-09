<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact',
        'email',
        'phone',
        'address',
        'branch_id',
        'is_shared'
    ];

    protected $casts = [
        'is_shared' => 'boolean'
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    // Scope to get suppliers for a specific branch
    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhere('is_shared', true);
        });
    }

    // Scope to get only shared suppliers
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    // Scope to get only branch-specific suppliers
    public function scopeBranchSpecific($query)
    {
        return $query->where('is_shared', false);
    }
}
