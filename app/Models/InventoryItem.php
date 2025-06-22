<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class InventoryItem extends Model
{
    use HasFactory, SoftDeletes, BranchAware;

    protected $fillable = [
        'name',
        'code',
        'description',
        'category_id',
        'unit',
        'unit_price',
        'reorder_point',
        'current_stock',
        'supplier_id',
        'branch_id',
        'status',
        'is_locked'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'current_stock' => 'decimal:2',
        'status' => 'string',
        'is_locked' => 'boolean'
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(InventorySupplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
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

    public function weeklyChecks()
    {
        return $this->hasMany(WeeklyStockCheck::class);
    }

    public function monthlyChecks()
    {
        return $this->hasMany(MonthlyStockCheck::class);
    }
} 