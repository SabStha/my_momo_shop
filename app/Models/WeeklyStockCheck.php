<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklyStockCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'branch_id',
        'quantity_checked',
        'checked_at',
        'notes',
        'audit_notes',
        'is_damaged',
        'is_missing',
        'image_path',
        'system_stock',
        'discrepancy_amount',
        'discrepancy_value',
        'audit_session_id',
        'audit_started_at',
        'audit_completed_at'
    ];

    protected $casts = [
        'checked_at' => 'date',
        'quantity_checked' => 'decimal:2',
        'system_stock' => 'decimal:2',
        'discrepancy_amount' => 'decimal:2',
        'discrepancy_value' => 'decimal:2',
        'is_damaged' => 'boolean',
        'is_missing' => 'boolean',
        'audit_started_at' => 'datetime',
        'audit_completed_at' => 'datetime'
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Helper methods for discrepancy calculations
    public function getDiscrepancyAmount()
    {
        if ($this->system_stock && $this->quantity_checked) {
            return $this->quantity_checked - $this->system_stock;
        }
        return 0;
    }

    public function getDiscrepancyValue()
    {
        $discrepancy = $this->getDiscrepancyAmount();
        if ($discrepancy && $this->inventoryItem) {
            return $discrepancy * $this->inventoryItem->unit_price;
        }
        return 0;
    }

    public function hasDiscrepancy()
    {
        return $this->getDiscrepancyAmount() != 0;
    }

    public function getDiscrepancyStatus()
    {
        $discrepancy = $this->getDiscrepancyAmount();
        if ($discrepancy > 0) {
            return 'overcount';
        } elseif ($discrepancy < 0) {
            return 'undercount';
        }
        return 'match';
    }
} 