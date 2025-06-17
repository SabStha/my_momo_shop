<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignTrigger extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'trigger_type', // 'behavioral', 'scheduled', 'segment'
        'trigger_condition', // JSON field for conditions
        'campaign_type', // 'email', 'sms', 'push'
        'campaign_template',
        'segment_id',
        'branch_id',
        'campaign_id',
        'is_active',
        'last_triggered_at',
        'next_scheduled_at',
        'frequency', // 'once', 'daily', 'weekly', 'monthly'
        'cooldown_period', // in hours
        'status',
        'action_taken',
        'opened_at',
        'clicked_at',
        'revenue_generated'
    ];

    protected $casts = [
        'trigger_condition' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'next_scheduled_at' => 'datetime',
        'cooldown_period' => 'integer',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'revenue_generated' => 'decimal:2'
    ];

    public function segment(): BelongsTo
    {
        return $this->belongsTo(CustomerSegment::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function campaignLogs()
    {
        return $this->hasMany(CampaignLog::class);
    }

    public function shouldTrigger()
    {
        if (!$this->is_active) {
            return false;
        }

        // Check cooldown period
        if ($this->last_triggered_at && 
            now()->diffInHours($this->last_triggered_at) < $this->cooldown_period) {
            return false;
        }

        // Check scheduled triggers
        if ($this->trigger_type === 'scheduled' && $this->next_scheduled_at) {
            return now()->gte($this->next_scheduled_at);
        }

        // Check behavioral triggers
        if ($this->trigger_type === 'behavioral') {
            return $this->checkBehavioralConditions();
        }

        // Check segment triggers
        if ($this->trigger_type === 'segment') {
            return $this->checkSegmentConditions();
        }

        return false;
    }

    private function checkBehavioralConditions()
    {
        $conditions = $this->trigger_condition;
        
        foreach ($conditions as $condition) {
            $users = $this->getUsersMatchingCondition($condition);
            if ($users->count() > 0) {
                return true;
            }
        }

        return false;
    }

    private function checkSegmentConditions()
    {
        if (!$this->segment) {
            return false;
        }

        $segmentSize = $this->segment->users()->count();
        $threshold = $this->trigger_condition['threshold'] ?? 0;

        return $segmentSize >= $threshold;
    }

    private function getUsersMatchingCondition($condition)
    {
        $query = User::query();

        switch ($condition['type']) {
            case 'purchase_frequency':
                $query->whereHas('orders', function ($q) use ($condition) {
                    $q->where('created_at', '>=', now()->subDays($condition['period']))
                        ->havingRaw('COUNT(*) >= ?', [$condition['count']]);
                });
                break;

            case 'spending_amount':
                $query->whereHas('orders', function ($q) use ($condition) {
                    $q->where('created_at', '>=', now()->subDays($condition['period']))
                        ->havingRaw('SUM(total_amount) >= ?', [$condition['amount']]);
                });
                break;

            case 'inactivity':
                $query->whereDoesntHave('orders', function ($q) use ($condition) {
                    $q->where('created_at', '>=', now()->subDays($condition['days']));
                });
                break;

            case 'cart_abandonment':
                $query->whereHas('cart', function ($q) use ($condition) {
                    $q->where('updated_at', '<=', now()->subHours($condition['hours']));
                });
                break;
        }

        return $query->get();
    }

    public function updateNextScheduledTime()
    {
        if ($this->trigger_type !== 'scheduled') {
            return;
        }

        $now = now();
        switch ($this->frequency) {
            case 'daily':
                $this->next_scheduled_at = $now->addDay();
                break;
            case 'weekly':
                $this->next_scheduled_at = $now->addWeek();
                break;
            case 'monthly':
                $this->next_scheduled_at = $now->addMonth();
                break;
            default:
                $this->next_scheduled_at = null;
        }

        $this->save();
    }
} 