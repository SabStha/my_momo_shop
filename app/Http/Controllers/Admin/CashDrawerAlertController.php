<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CashDrawerAlertService;
use Illuminate\Support\Facades\Auth;

class CashDrawerAlertController extends Controller
{
    protected $alertService;

    public function __construct(CashDrawerAlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    /**
     * Get all alerts for a branch
     */
    public function index(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer'
        ]);

        $alerts = $this->alertService->getBranchAlerts($request->branch_id);
        
        return response()->json([
            'alerts' => $alerts
        ]);
    }

    /**
     * Update alert thresholds
     */
    public function update(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'denomination' => 'required|integer',
            'low_threshold' => 'required|integer|min:0',
            'high_threshold' => 'required|integer|min:0|gte:low_threshold'
        ]);

        try {
            $alert = $this->alertService->updateAlertThresholds(
                $request->branch_id,
                $request->denomination,
                $request->low_threshold,
                $request->high_threshold
            );

            return response()->json([
                'message' => 'Alert thresholds updated successfully',
                'alert' => $alert
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update alert thresholds: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle alert status
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'denomination' => 'required|integer',
            'is_active' => 'required|boolean'
        ]);

        try {
            $alert = $this->alertService->toggleAlert(
                $request->branch_id,
                $request->denomination,
                $request->is_active
            );

            if (!$alert) {
                return response()->json([
                    'message' => 'Alert not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Alert status updated successfully',
                'alert' => $alert
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update alert status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current alerts for a branch
     */
    public function getCurrentAlerts(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'denominations' => 'required|array'
        ]);

        try {
            $alertSummary = $this->alertService->getAlertSummary(
                $request->branch_id,
                $request->denominations
            );

            return response()->json($alertSummary);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get current alerts: ' . $e->getMessage()
            ], 500);
        }
    }
}
