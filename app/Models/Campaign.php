<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'description',
        'segment_id',
        'offer_type',
        'offer_value',
        'copy',
        'targeting_criteria',
        'start_date',
        'end_date',
        'status',
        'target_customers',
        'reached_customers',
        'converted_customers',
        'total_revenue',
        'roi',
        'metrics',
        'cost'
    ];

    protected $casts = [
        'targeting_criteria' => 'array',
        'metrics' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'total_revenue' => 'decimal:2',
        'roi' => 'decimal:2',
        'cost' => 'decimal:2'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function segment()
    {
        return $this->belongsTo(CustomerSegment::class);
    }

    public function triggers(): HasMany
    {
        return $this->hasMany(CampaignTrigger::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function isActive()
    {
        return $this->status === 'active' && 
               now()->between($this->start_date, $this->end_date);
    }

    public function isScheduled()
    {
        return $this->status === 'scheduled' && 
               now()->lt($this->start_date);
    }

    public function isCompleted()
    {
        return $this->status === 'completed' || 
               now()->gt($this->end_date);
    }

    public function getConversionRateAttribute()
    {
        if ($this->reached_customers === 0) {
            return 0;
        }
        return ($this->converted_customers / $this->reached_customers) * 100;
    }

    public function getAverageOrderValueAttribute()
    {
        if ($this->converted_customers === 0) {
            return 0;
        }
        return $this->total_revenue / $this->converted_customers;
    }

    public function updateMetrics(array $metrics)
    {
        $this->metrics = array_merge($this->metrics ?? [], $metrics);
        $this->save();
    }

    public function calculateROI()
    {
        if ($this->cost === 0) {
            return 0;
        }
        return (($this->total_revenue - $this->cost) / $this->cost) * 100;
    }
} 