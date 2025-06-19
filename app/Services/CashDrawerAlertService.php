<?php

namespace App\Services;

use App\Models\CashDrawerAlert;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;

class CashDrawerAlertService
{
    /**
     * Check alerts for current denominations
     */
    public function checkAlerts($branchId, $currentDenominations)
    {
        $alerts = [];
        $branch = Branch::find($branchId);
        
        if (!$branch) {
            return $alerts;
        }

        // Get all active alerts for this branch
        $branchAlerts = CashDrawerAlert::where('branch_id', $branchId)
            ->where('is_active', true)
            ->get()
            ->keyBy('denomination');

        foreach ($currentDenominations as $denomination => $count) {
            if (isset($branchAlerts[$denomination])) {
                $alert = $branchAlerts[$denomination];
                
                // Skip low alerts for Rs 1000 (highest denomination)
                if ($denomination == 1000) {
                    // Only check for high alerts for Rs 1000
                    if ($alert->isHighAlert($count)) {
                        $alerts[] = [
                            'denomination' => $denomination,
                            'current_count' => $count,
                            'threshold' => $alert->high_threshold,
                            'status' => 'high',
                            'message' => $this->getAlertMessage($denomination, $count, 'high', $alert),
                            'severity' => 'info'
                        ];
                    }
                } else {
                    // Check both low and high alerts for other denominations
                    $status = $alert->getAlertStatus($count);
                    
                    if ($status !== 'normal') {
                        $alerts[] = [
                            'denomination' => $denomination,
                            'current_count' => $count,
                            'threshold' => $status === 'low' ? $alert->low_threshold : $alert->high_threshold,
                            'status' => $status,
                            'message' => $this->getAlertMessage($denomination, $count, $status, $alert),
                            'severity' => $status === 'low' ? 'warning' : 'info'
                        ];
                    }
                }
            }
        }

        return $alerts;
    }

    /**
     * Get alert message
     */
    private function getAlertMessage($denomination, $count, $status, $alert)
    {
        if ($status === 'low') {
            return "Rs {$denomination} notes are running low! Current: {$count}, Minimum: {$alert->low_threshold}";
        } else {
            return "Rs {$denomination} notes are high! Current: {$count}, Maximum: {$alert->high_threshold}";
        }
    }

    /**
     * Get alert summary for display
     */
    public function getAlertSummary($branchId, $currentDenominations)
    {
        $alerts = $this->checkAlerts($branchId, $currentDenominations);
        
        $lowAlerts = collect($alerts)->where('status', 'low')->count();
        $highAlerts = collect($alerts)->where('status', 'high')->count();
        
        return [
            'total_alerts' => count($alerts),
            'low_alerts' => $lowAlerts,
            'high_alerts' => $highAlerts,
            'has_alerts' => count($alerts) > 0,
            'alerts' => $alerts
        ];
    }

    /**
     * Update alert thresholds
     */
    public function updateAlertThresholds($branchId, $denomination, $lowThreshold, $highThreshold)
    {
        return CashDrawerAlert::updateOrCreate(
            [
                'branch_id' => $branchId,
                'denomination' => $denomination,
            ],
            [
                'low_threshold' => $lowThreshold,
                'high_threshold' => $highThreshold,
                'is_active' => true,
            ]
        );
    }

    /**
     * Toggle alert status
     */
    public function toggleAlert($branchId, $denomination, $isActive)
    {
        $alert = CashDrawerAlert::where('branch_id', $branchId)
            ->where('denomination', $denomination)
            ->first();

        if ($alert) {
            $alert->update(['is_active' => $isActive]);
            return $alert;
        }

        return null;
    }

    /**
     * Get all alerts for a branch
     */
    public function getBranchAlerts($branchId)
    {
        return CashDrawerAlert::where('branch_id', $branchId)
            ->orderBy('denomination')
            ->get();
    }
} 