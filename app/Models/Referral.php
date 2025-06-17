<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'referred_user_id',
        'code',
        'status'
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class, 'creator_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (static::where('code', $code)->exists());

        return $code;
    }
} 