<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerSegment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'criteria',
        'customer_count',
        'average_value',
        'is_active'
    ];

    protected $casts = [
        'criteria' => 'array',
        'average_value' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'customer_segment', 'name');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function updateCustomerCount()
    {
        $this->customer_count = $this->customers()->count();
        $this->save();
    }

    public function updateAverageValue()
    {
        $this->average_value = $this->customers()->avg('total_spent') ?? 0;
        $this->save();
    }
} 