<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorEarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'amount',
        'type',
        'description',
        'expires_at',
        'status'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
} 