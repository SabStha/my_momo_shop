<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'notify_orders',
        'notify_offers',
        'theme',
        'language'
    ];

    protected $casts = [
        'notify_orders' => 'boolean',
        'notify_offers' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 