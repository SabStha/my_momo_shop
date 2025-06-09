<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BranchAware;

class InventoryOrder extends Model
{
    use HasFactory, SoftDeletes, BranchAware;

    protected $fillable = [
        'order_number',
        'supplier_id',
        'branch_id',
        'status',
        'ordered_at',
        'sent_at',
        'received_at',
        'total_amount',
        'notes',
        'user_id'
    ];

    protected $casts = [
        'ordered_at' => 'datetime',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'status' => 'string'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_SENT,
            self::STATUS_RECEIVED,
            self::STATUS_CANCELLED
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryOrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalAmountAttribute()
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::where('order_number', 'like', $prefix . $date . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }
} 