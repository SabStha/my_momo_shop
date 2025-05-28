<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Creator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'bio',
        'avatar',
        'referral_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 