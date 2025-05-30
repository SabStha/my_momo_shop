<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashout extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id', 'points', 'amount', 'status',
    ];

    public function creator()
    {
        return $this->belongsTo(Creator::class);
    }
}
