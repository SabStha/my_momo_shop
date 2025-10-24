<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutomatedOfferTrigger;
use App\Services\AutomatedOfferTriggerService;
use App\Services\OfferAnalyticsService;
use App\Services\ABTestingService;
use Illuminate\Http\Request;

class AutomatedOfferController extends Controller
{
    protected $triggerService;
    protected $analyticsService;
    protected $abTestingService;

    public function __construct(
        AutomatedOfferTriggerService $triggerService,
        OfferAnalyticsService $analyticsService,
        ABTestingService $abTestingService
    ) {
        $this->triggerService = $triggerService;
        $this->analyticsService = $analyticsService;
        $this->abTestingService = $abTestingService;
    }

    /**
     * Get all automated triggers
     */
    public function index()
    {
        $triggers = AutomatedOfferTrigger::orderBy('priority', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'triggers' => $triggers,
        ]);
    }

    /**
     * Get analytics dashboard data
     */
    public function analytics(Request $request)
    {
        $startDate = $request->input('start_date') ? \Carbon\Carbon::parse($request->input('start_date')) : null;
        $endDate = $request->input('end_date') ? \Carbon\Carbon::parse($request->input('end_date')) : null;
        
        $dashboardData = $this->analyticsService->getDashboardData($startDate, $endDate);
        $realTimeStats = $this->analyticsService->getRealTimeStats();
        
        return response()->json([
            'success' => true,
            'dashboard' => $dashboardData,
            'real_time' => $realTimeStats,
        ]);
    }

    /**
     * Process triggers manually
     */
    public function process Processing(Request $request)
    {
        $triggerType = $request->input('trigger_type');
        
        if ($triggerType) {
            $result = $this->triggerService->processTriggerType($triggerType);
        } else {
            $result = $this->triggerService->processAllTriggers();
        }
        
        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }

    /**
     * Update trigger
     */
    public function update(Request $request, AutomatedOfferTrigger $trigger)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'conditions' => 'sometimes|array',
            'offer_template' => 'sometimes|array',
            'priority' => 'sometimes|integer|min:1|max:10',
            'is_active' => 'sometimes|boolean',
            'max_uses_per_user' => 'nullable|integer',
            'cooldown_days' => 'sometimes|integer|min:1',
        ]);

        $trigger->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Trigger updated successfully',
            'trigger' => $trigger,
        ]);
    }

    /**
     * Toggle trigger status
     */
    public function toggleStatus(AutomatedOfferTrigger $trigger)
    {
        $trigger->is_active = !$trigger->is_active;
        $trigger->save();

        return response()->json([
            'success' => true,
            'message' => 'Trigger ' . ($trigger->is_active ? 'activated' : 'deactivated'),
            'trigger' => $trigger,
        ]);
    }

    /**
     * Create A/B test
     */
    public function createABTest(Request $request)
    {
        $validated = $request->validate([
            'variant_a' => 'required|array',
            'variant_b' => 'required|array',
            'config' => 'sometimes|array',
        ]);

        $test = $this->abTestingService->createABTest(
            $validated['variant_a'],
            $validated['variant_b'],
            $validated['config'] ?? []
        );

        return response()->json([
            'success' => true,
            'message' => 'A/B test created successfully',
            'test' => $test,
        ]);
    }

    /**
     * Get A/B test results
     */
    public function getABTestResults(Request $request, string $testId)
    {
        $results = $this->abTestingService->getTestResults($testId);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Get trigger performance stats
     */
    public function triggerStats(AutomatedOfferTrigger $trigger)
    {
        $offers = \App\Models\Offer::where('type', $trigger->trigger_type)->get();
        
        $stats = [
            'total_offers_created' => $offers->count(),
            'total_claims' => $offers->sum(function($offer) {
                return $offer->claims()->count();
            }),
            'total_redemptions' => $offers->sum(function($offer) {
                return $offer->claims()->where('status', 'used')->count();
            }),
        ];

        return response()->json([
            'success' => true,
            'trigger' => $trigger,
            'stats' => $stats,
        ]);
    }
}

