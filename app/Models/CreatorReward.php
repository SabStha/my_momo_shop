<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatorReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id', 'badge', 'reward', 'month', 'claimed', 'claimed_at'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
} 