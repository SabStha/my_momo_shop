<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class InventoryItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'description',
        'category_code',
        'quantity',
        'unit',
        'unit_price',
        'reorder_point',
        'safety_stock',
        'location',
        'supplier_id',
        'last_restock_date',
        'next_restock_date',
        'status',
        'is_locked'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'safety_stock' => 'decimal:2',
        'last_restock_date' => 'date',
        'next_restock_date' => 'date',
        'is_locked' => 'boolean'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_code', 'code');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function needsRestock(): bool
    {
        return $this->quantity <= $this->reorder_point;
    }

    public function updateQuantity(float $quantity, string $type, ?string $notes = null): void
    {
        $oldQuantity = $this->quantity;
        $newQuantity = match ($type) {
            'purchase', 'return' => $oldQuantity + $quantity,
            'sale', 'waste' => $oldQuantity - $quantity,
            'adjustment' => $quantity,
            default => throw new \InvalidArgumentException('Invalid transaction type'),
        };

        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('Insufficient stock for this transaction');
        }

        $this->quantity = $newQuantity;
        $this->save();

        // Create transaction record
        $this->transactions()->create([
            'type' => $type,
            'quantity' => $quantity,
            'unit_price' => $this->unit_price,
            'total_amount' => $quantity * $this->unit_price,
            'notes' => $notes,
            'user_id' => auth()->id(),
        ]);

        // Update last restock date if this is a purchase
        if ($type === 'purchase') {
            $this->last_restock_date = now();
            $this->save();
        }
    }

    public function dailyChecks()
    {
        return $this->hasMany(DailyStockCheck::class);
    }
} 