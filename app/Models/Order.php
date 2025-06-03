<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'type',
        'table_id',
        'status',
        'payment_status',
        'payment_method',
        'guest_name',
        'guest_email',
        'user_id',
        'created_by',
        'shipping_address',
        'billing_address',
    ];

    /**
     * Attributes that should be guarded from mass assignment
     * These are critical financial fields that should be calculated, not directly assigned
     */
    protected $guarded = [
        'id',
        'total_amount',
        'tax_amount', 
        'grand_total',
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
        'change' => 'decimal:2'
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'warning'
        };
    }
} 