<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChurnPrediction extends Model
{
    protected $fillable = [
        'customer_id',
        'churn_probability',
        'risk_factors',
        'last_updated',
        'branch_id'
    ];

    protected $casts = [
        'churn_probability' => 'float',
        'risk_factors' => 'array',
        'last_updated' => 'datetime'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
} 