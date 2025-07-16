<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, BranchAware, SoftDeletes;

    protected $fillable = [
        'user_id',
        'created_by',
        'table_id',
        'order_type',
        'status',
        'payment_status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'wallet_payment',
        'cash_payment',
        'shipping_address',
        'notes',
        'payment_method',
        'guest_name',
        'order_number',
        'branch_id',
        'total_amount',
        'profit',
        'delivery_address',
        'delivery_status',
        'delivery_time',
        'delivery_fee',
        'tax_amount',
        'discount_amount',
        'grand_total',
        'delivered_at',
        'credits_account_id',
        'customer_name',
        'customer_email',
        'customer_phone'
    ];

    /**
     * Attributes that should be guarded from mass assignment
     * These are critical financial fields that should be calculated, not directly assigned
     */
    protected $guarded = [
        'id',
        'amount_received',
        'change',
        'paid_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'wallet_payment' => 'decimal:2',
        'cash_payment' => 'decimal:2',
        'amount_received' => 'decimal:2',
        'change' => 'decimal:2',
        'shipping_address' => 'array',
        'delivery_address' => 'array',
        'total_amount' => 'decimal:2',
        'profit' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'delivery_time' => 'datetime',
        'delivered_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }
    // In Order.php
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creditsAccount()
    {
        return $this->belongsTo(\App\Models\Wallet::class, 'credits_account_id');
    }

    public function calculateTotals()
    {
        $subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $this->subtotal = $subtotal;
        $this->total_amount = $subtotal + $this->delivery_fee + $this->tax_amount - $this->discount_amount;
        $this->grand_total = $this->total_amount;

        // Calculate profit
        $this->profit = $this->items->sum(function ($item) {
            return ($item->price - $item->product->cost) * $item->quantity;
        });

        $this->save();
    }
} 