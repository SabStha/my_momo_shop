<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashDrawerLog extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'action',
        'reason',
        'status',
        'error_message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
} 