<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CustomerSegment;
use App\Services\CampaignService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class CampaignController extends Controller
{
    protected $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    /**
     * Display a listing of campaigns
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->input('status');
        $branchId = $request->input('branch_id', 1);
        
        $query = Campaign::where('branch_id', $branchId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $campaigns = $query->with('segment')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json([
            'status' => 'success',
            'data' => $campaigns
        ]);
    }

    /**
     * Store a newly created campaign
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'segment_id' => 'required|exists:customer_segments,id',
            'offer_type' => 'required|string',
            'offer_value' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'targeting_criteria' => 'nullable|array'
        ]);

        $validated['branch_id'] = $request->input('branch_id', 1);
        
        try {
            $campaign = $this->campaignService->createCampaign($validated);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Campaign created successfully',
                'data' => $campaign
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified campaign
     */
    public function show(Campaign $campaign): JsonResponse
    {
        $campaign->load('segment');
        
        return response()->json([
            'status' => 'success',
            'data' => $campaign
        ]);
    }

    /**
     * Update the specified campaign
     */
    public function update(Request $request, Campaign $campaign): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'segment_id' => 'sometimes|required|exists:customer_segments,id',
            'offer_type' => 'sometimes|required|string',
            'offer_value' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'status' => 'sometimes|required|in:draft,scheduled,active,paused,completed,cancelled',
            'targeting_criteria' => 'nullable|array'
        ]);

        try {
            $campaign->update($validated);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Campaign updated successfully',
                'data' => $campaign
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified campaign
     */
    public function destroy(Campaign $campaign): JsonResponse
    {
        try {
            $campaign->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Campaign deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting campaign: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campaign suggestions for a segment
     */
    public function getSuggestions(Request $request): JsonResponse
    {
        $segmentId = $request->input('segment_id');
        
        try {
            $suggestions = $this->campaignService->getCampaignSuggestions($segmentId);
            
            return response()->json([
                'status' => 'success',
                'data' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error getting campaign suggestions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campaign metrics
     */
    public function getMetrics(Campaign $campaign): JsonResponse
    {
        try {
            $metrics = [
                'conversion_rate' => $campaign->conversion_rate,
                'average_order_value' => $campaign->average_order_value,
                'roi' => $campaign->calculateROI(),
                'reached_customers' => $campaign->reached_customers,
                'converted_customers' => $campaign->converted_customers,
                'total_revenue' => $campaign->total_revenue
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error getting campaign metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update campaign status
     */
    public function updateStatus(Request $request, Campaign $campaign): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,scheduled,active,paused,completed,cancelled'
        ]);

        try {
            $campaign->update($validated);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Campaign status updated successfully',
                'data' => $campaign
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating campaign status: ' . $e->getMessage()
            ], 500);
        }
    }
} 