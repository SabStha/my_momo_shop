<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total',
        'status',
        'payment_status',
<<<<<<< HEAD
        'shipping_address',
        'notes'
=======
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
>>>>>>> 8796716812b1e01816250aacc94a253623170fcd
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'shipping_address' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
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
} 