<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAmendment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'amended_by',
        'items_removed',
        'items_added',
        'amount_change',
        'reason',
    ];

    protected $casts = [
        'items_removed' => 'array',
        'items_added'   => 'array',
        'amount_change' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function amendedBy()
    {
        return $this->belongsTo(User::class, 'amended_by');
    }
}
