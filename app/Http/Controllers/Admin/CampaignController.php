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
    public function index()
    {
        $campaigns = Campaign::where('branch_id', session('selected_branch_id'))
            ->with('segment')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.campaigns.index', compact('campaigns'));
    }

    /**
     * Show the form for creating a new campaign
     */
    public function create()
    {
        $segments = CustomerSegment::where('branch_id', session('selected_branch_id'))->get();
        return view('admin.campaigns.create', compact('segments'));
    }

    /**
     * Store a newly created campaign
     */
    public function store(Request $request)
    {
        try {
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

            if (!session('selected_branch_id')) {
                throw new \Exception('No branch selected. Please select a branch first.');
            }

            $validated['branch_id'] = session('selected_branch_id');
            $validated['status'] = 'active';
            
            \Log::info('Creating campaign with data:', $validated);
            
            $campaign = $this->campaignService->createCampaign($validated);
            
            \Log::info('Campaign created successfully:', ['campaign_id' => $campaign->id]);
            
            return redirect()->route('admin.campaigns.index')
                ->with('success', 'Campaign created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error creating campaign:', ['errors' => $e->errors()]);
            return back()->withInput()->withErrors($e->errors());
        } catch (\Exception $e) {
            \Log::error('Error creating campaign:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withInput()
                ->withErrors(['error' => 'Error creating campaign: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified campaign
     */
    public function show(Campaign $campaign)
    {
        $campaign->load('segment');
        return view('admin.campaigns.show', compact('campaign'));
    }

    /**
     * Show the form for editing the specified campaign
     */
    public function edit(Campaign $campaign)
    {
        $segments = CustomerSegment::where('branch_id', session('selected_branch_id'))->get();
        return view('admin.campaigns.edit', compact('campaign', 'segments'));
    }

    /**
     * Update the specified campaign
     */
    public function update(Request $request, Campaign $campaign)
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

        try {
            $campaign->update($validated);
            
            return redirect()->route('admin.campaigns.index')
                ->with('success', 'Campaign updated successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Error updating campaign: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified campaign
     */
    public function destroy(Campaign $campaign)
    {
        try {
            $campaign->delete();
            
            return redirect()->route('admin.campaigns.index')
                ->with('success', 'Campaign deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error deleting campaign: ' . $e->getMessage()]);
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
    public function updateStatus(Request $request, Campaign $campaign)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,scheduled,active,paused,completed,cancelled'
        ]);

        try {
            $campaign->update($validated);
            
            return redirect()->route('admin.campaigns.index')
                ->with('success', 'Campaign status updated successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error updating campaign status: ' . $e->getMessage()]);
        }
    }
} 