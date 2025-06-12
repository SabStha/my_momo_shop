<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, BranchAware;

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
        'branch_id'
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
        'shipping_address' => 'array'
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

} 