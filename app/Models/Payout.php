<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'amount',
        'status',
        'requested_at',
        'processed_at',
        'payment_method',
        'payment_details',
        'notes'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'payment_details' => 'array'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
} 