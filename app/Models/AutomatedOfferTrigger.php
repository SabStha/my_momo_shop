<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomatedOfferTrigger extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'trigger_type',
        'description',
        'conditions',
        'offer_template',
        'priority',
        'is_active',
        'max_uses_per_user',
        'cooldown_days',
    ];

    protected $casts = [
        'conditions' => 'array',
        'offer_template' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('trigger_type', $type);
    }
}
