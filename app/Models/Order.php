<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'table_id',
        'type',
        'status',
        'payment_status',
        'total_amount',
        'tax_amount',
        'grand_total',
        'shipping_address',
        'notes',
        'payment_method',
        'guest_name',
        'order_number'
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
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
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