<?php

namespace App\Models;

use App\Traits\BranchAware;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory, BranchAware;

    protected $fillable = [
        'name',
        'capacity',
        'status',
        'branch_id',
        'is_active'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
} 