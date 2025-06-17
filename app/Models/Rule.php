<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'conditions',
        'actions',
        'priority',
        'is_active',
        'branch_id',
        'created_by'
    ];

    protected $casts = [
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
        'priority' => 'integer'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority');
    }

    public function evaluate($customer)
    {
        foreach ($this->conditions as $condition) {
            if (!$this->evaluateCondition($condition, $customer)) {
                return false;
            }
        }
        return true;
    }

    private function evaluateCondition($condition, $customer)
    {
        switch ($condition['type']) {
            case 'risk_level':
                return $customer->risk_level === $condition['value'];
            
            case 'vip_status':
                return $customer->is_vip === ($condition['value'] === 'true');
            
            case 'purchase_frequency':
                $count = $customer->orders()
                    ->where('created_at', '>=', now()->subDays($condition['period']))
                    ->count();
                return $this->compare($count, $condition['operator'], $condition['value']);
            
            case 'spending_amount':
                $amount = $customer->orders()
                    ->where('created_at', '>=', now()->subDays($condition['period']))
                    ->sum('total_amount');
                return $this->compare($amount, $condition['operator'], $condition['value']);
            
            case 'last_purchase':
                $lastPurchase = $customer->orders()
                    ->latest()
                    ->first();
                if (!$lastPurchase) {
                    return false;
                }
                $daysSinceLastPurchase = now()->diffInDays($lastPurchase->created_at);
                return $this->compare($daysSinceLastPurchase, $condition['operator'], $condition['value']);
            
            default:
                return false;
        }
    }

    private function compare($value, $operator, $compareValue)
    {
        switch ($operator) {
            case 'equals':
                return $value == $compareValue;
            case 'not_equals':
                return $value != $compareValue;
            case 'greater_than':
                return $value > $compareValue;
            case 'less_than':
                return $value < $compareValue;
            case 'greater_than_or_equal':
                return $value >= $compareValue;
            case 'less_than_or_equal':
                return $value <= $compareValue;
            default:
                return false;
        }
    }

    public function execute($customer)
    {
        foreach ($this->actions as $action) {
            $this->executeAction($action, $customer);
        }
    }

    private function executeAction($action, $customer)
    {
        switch ($action['type']) {
            case 'launch_campaign':
                $campaign = Campaign::find($action['campaign_id']);
                if ($campaign) {
                    $campaign->triggers()->create([
                        'customer_id' => $customer->id,
                        'status' => 'pending',
                        'trigger_type' => 'rule',
                        'rule_id' => $this->id
                    ]);
                }
                break;
            
            case 'update_customer':
                $customer->update($action['updates']);
                break;
            
            case 'send_notification':
                // Implement notification sending
                break;
        }
    }
} 