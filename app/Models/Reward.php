<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'type',
        'amount',
        'month',
        'claimed',
        'claimed_at',
        'notes'
    ];

    protected $casts = [
        'month' => 'date',
        'claimed' => 'boolean',
        'claimed_at' => 'datetime'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
} 