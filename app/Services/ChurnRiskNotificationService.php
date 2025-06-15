<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class ChurnRiskNotificationService
{
    public function checkChurnRisks()
    {
        $notifications = [];
        $branches = Branch::all();

        foreach ($branches as $branch) {
            $riskLevel = $this->calculateBranchChurnRisk($branch);
            
            if ($riskLevel['level'] !== 'low') {
                $notifications[] = [
                    'type' => $riskLevel['level'],
                    'title' => "Churn Risk Alert: {$branch->name}",
                    'message' => $riskLevel['message'],
                    'branch_id' => $branch->id,
                    'timestamp' => now()
                ];
            }
        }

        return $notifications;
    }

    private function calculateBranchChurnRisk(Branch $branch)
    {
        // Get customers who haven't ordered in the last 30 days
        $inactiveCustomers = Customer::where('branch_id', $branch->id)
            ->whereDoesntHave('orders', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })
            ->count();

        // Get total active customers (ordered in last 90 days)
        $activeCustomers = Customer::where('branch_id', $branch->id)
            ->whereHas('orders', function ($query) {
                $query->where('created_at', '>=', now()->subDays(90));
            })
            ->count();

        if ($activeCustomers === 0) {
            return [
                'level' => 'low',
                'message' => 'No active customers to analyze'
            ];
        }

        $churnRate = ($inactiveCustomers / $activeCustomers) * 100;

        if ($churnRate >= 30) {
            return [
                'level' => 'danger',
                'message' => "High churn risk detected! {$inactiveCustomers} customers haven't ordered in 30 days. Churn rate: {$churnRate}%"
            ];
        } elseif ($churnRate >= 20) {
            return [
                'level' => 'warning',
                'message' => "Moderate churn risk detected. {$inactiveCustomers} customers haven't ordered in 30 days. Churn rate: {$churnRate}%"
            ];
        }

        return [
            'level' => 'low',
            'message' => "Low churn risk. Current churn rate: {$churnRate}%"
        ];
    }

    public function getCachedNotifications()
    {
        return Cache::remember('churn_risk_notifications', 3600, function () {
            return $this->checkChurnRisks();
        });
    }
} 