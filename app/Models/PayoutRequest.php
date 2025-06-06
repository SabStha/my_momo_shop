<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id', 'amount', 'status', 'requested_at', 'processed_at'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
} 