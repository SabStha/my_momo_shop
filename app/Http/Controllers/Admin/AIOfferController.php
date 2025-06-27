<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AIOfferService;
use App\Models\Offer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AIOfferController extends Controller
{
    protected $aiOfferService;

    public function __construct(AIOfferService $aiOfferService)
    {
        $this->aiOfferService = $aiOfferService;
    }

    /**
     * Show AI offers management page
     */
    public function index()
    {
        $aiOffers = Offer::aiGenerated()
            ->with(['branch', 'user'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total_ai_offers' => Offer::aiGenerated()->count(),
            'active_ai_offers' => Offer::aiGenerated()->active()->count(),
            'personalized_offers' => Offer::personalized()->count(),
            'total_offers' => Offer::count(),
        ];

        return view('admin.ai-offers.index', compact('aiOffers', 'stats'));
    }

    /**
     * Generate new AI offers
     */
    public function generate(Request $request)
    {
        try {
            $branchId = $request->input('branch_id', 1);
            
            $result = $this->aiOfferService->generateAIOffers($branchId);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully generated {$result['offers_created']} AI offers!",
                    'offers' => $result['offers']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate AI offers: ' . $result['error']
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('AI Offer Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating AI offers'
            ], 500);
        }
    }

    /**
     * Generate personalized offers for a specific user
     */
    public function generatePersonalized(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $branchId = $request->input('branch_id', 1);
            
            $user = User::findOrFail($userId);
            
            $result = $this->aiOfferService->generatePersonalizedOffers($user, $branchId);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Personalized offers generated successfully!',
                    'offers' => $result['offers']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate personalized offers: ' . $result['error']
                ], 500);
            }
            
        } catch (\Exception $e) {
            Log::error('Personalized Offer Generation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating personalized offers'
            ], 500);
        }
    }

    /**
     * Show AI offer details
     */
    public function show(Offer $offer)
    {
        if (!$offer->ai_generated) {
            return redirect()->route('admin.offers.index')
                ->with('error', 'This is not an AI-generated offer');
        }

        return view('admin.ai-offers.show', compact('offer'));
    }

    /**
     * Edit AI offer
     */
    public function edit(Offer $offer)
    {
        if (!$offer->ai_generated) {
            return redirect()->route('admin.offers.index')
                ->with('error', 'This is not an AI-generated offer');
        }

        return view('admin.ai-offers.edit', compact('offer'));
    }

    /**
     * Update AI offer
     */
    public function update(Request $request, Offer $offer)
    {
        if (!$offer->ai_generated) {
            return redirect()->route('admin.offers.index')
                ->with('error', 'This is not an AI-generated offer');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'discount' => 'required|numeric|min:0|max:100',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        $data['is_active'] = $request->has('is_active');

        $offer->update($data);

        return redirect()->route('admin.ai-offers.index')
            ->with('success', 'AI offer updated successfully!');
    }

    /**
     * Delete AI offer
     */
    public function destroy(Offer $offer)
    {
        if (!$offer->ai_generated) {
            return redirect()->route('admin.offers.index')
                ->with('error', 'This is not an AI-generated offer');
        }

        $offer->delete();

        return redirect()->route('admin.ai-offers.index')
            ->with('success', 'AI offer deleted successfully!');
    }

    /**
     * Toggle AI offer status
     */
    public function toggleStatus(Offer $offer)
    {
        if (!$offer->ai_generated) {
            return response()->json([
                'success' => false,
                'message' => 'This is not an AI-generated offer'
            ], 400);
        }

        $offer->update(['is_active' => !$offer->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Offer status updated successfully!',
            'is_active' => $offer->is_active
        ]);
    }

    /**
     * Get AI offer analytics
     */
    public function analytics()
    {
        $analytics = [
            'ai_offers_performance' => $this->getAIOffersPerformance(),
            'personalized_offers_stats' => $this->getPersonalizedOffersStats(),
            'offer_types_distribution' => $this->getOfferTypesDistribution(),
            'ai_generation_trends' => $this->getAIGenerationTrends(),
        ];

        return view('admin.ai-offers.analytics', compact('analytics'));
    }

    /**
     * Get AI offers performance data
     */
    protected function getAIOffersPerformance()
    {
        $aiOffers = Offer::aiGenerated()
            ->withCount(['orders' => function($query) {
                $query->where('status', 'completed');
            }])
            ->get();

        return [
            'total_offers' => $aiOffers->count(),
            'active_offers' => $aiOffers->where('is_active', true)->count(),
            'total_usage' => $aiOffers->sum('orders_count'),
            'average_usage' => $aiOffers->avg('orders_count'),
            'top_performing' => $aiOffers->sortByDesc('orders_count')->take(5),
        ];
    }

    /**
     * Get personalized offers statistics
     */
    protected function getPersonalizedOffersStats()
    {
        $personalizedOffers = Offer::personalized()
            ->with(['user', 'orders'])
            ->get();

        return [
            'total_personalized' => $personalizedOffers->count(),
            'active_personalized' => $personalizedOffers->where('is_active', true)->count(),
            'users_with_offers' => $personalizedOffers->unique('user_id')->count(),
            'average_usage_per_user' => $personalizedOffers->avg('orders_count'),
        ];
    }

    /**
     * Get offer types distribution
     */
    protected function getOfferTypesDistribution()
    {
        return Offer::aiGenerated()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');
    }

    /**
     * Get AI generation trends
     */
    protected function getAIGenerationTrends()
    {
        return Offer::aiGenerated()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');
    }

    /**
     * Get users for personalized offers
     */
    public function getUsers(Request $request)
    {
        $search = $request->input('search');
        
        $users = User::where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
} 