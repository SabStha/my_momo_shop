<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'offer_id',
        'user_id',
        'action',
        'timestamp',
        'device_info',
        'session_data',
        'notification_id',
        'discount_value',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'device_info' => 'array',
        'session_data' => 'array',
        'discount_value' => 'decimal:2',
    ];

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notification()
    {
        return $this->belongsTo(\Illuminate\Notifications\DatabaseNotification::class, 'notification_id');
    }
}
