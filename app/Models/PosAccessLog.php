<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'access_type', // 'pos' or 'payment_manager'
        'action', // 'login', 'logout', 'order', 'payment'
        'details',
        'ip_address'
    ];

    protected $casts = [
        'details' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
