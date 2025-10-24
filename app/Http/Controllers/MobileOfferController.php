<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferClaim;
use App\Services\UserBehaviorAnalyzer;
use App\Services\OfferAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileOfferController extends Controller
{
    protected $behaviorAnalyzer;
    protected $analyticsService;

    public function __construct(
        UserBehaviorAnalyzer $behaviorAnalyzer,
        OfferAnalyticsService $analyticsService
    ) {
        $this->behaviorAnalyzer = $behaviorAnalyzer;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get personalized offer recommendations for user
     */
    public function recommendations(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // Get user behavior profile
        $profile = $this->behaviorAnalyzer->getUserBehaviorProfile($user);
        
        // Get optimal offer prediction
        $optimalOffer = $this->behaviorAnalyzer->predictOptimalOffer($user);
        
        // Get available offers for user
        $availableOffers = Offer::active()
            ->forUser($user->id)
            ->notClaimedBy($user->id)
            ->get();
        
        // Rank offers by relevance
        $rankedOffers = $this->rankOffersByRelevance($availableOffers, $profile);
        
        return response()->json([
            'success' => true,
            'recommendations' => [
                'top_offers' => $rankedOffers->take(5),
                'user_profile' => [
                    'tier' => $profile['value_metrics']['value_tier'],
                    'engagement_score' => $profile['engagement_score'],
                    'churn_risk' => $profile['churn_risk'],
                    'preferences' => $profile['product_preferences']['favorite_categories'] ?? [],
                ],
                'optimal_offer_suggestion' => $optimalOffer,
            ],
        ]);
    }

    /**
     * Track offer view
     */
    public function trackView(Request $request, Offer $offer)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $this->analyticsService->trackAction($offer, $user, 'viewed', [
            'device_info' => $request->header('User-Agent'),
            'session_data' => ['source' => $request->input('source', 'app')],
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Get offer details with personalized context
     */
    public function show(Request $request, Offer $offer)
    {
        $user = Auth::user();
        
        $offerData = [
            'id' => $offer->id,
            'title' => $offer->title,
            'description' => $offer->description,
            'code' => $offer->code,
            'discount' => $offer->discount,
            'type' => $offer->type,
            'min_purchase' => $offer->min_purchase,
            'max_discount' => $offer->max_discount,
            'valid_until' => $offer->valid_until,
            'is_active' => $offer->is_active,
        ];

        if ($user) {
            // Check if already claimed
            $claim = OfferClaim::where('user_id', $user->id)
                ->where('offer_id', $offer->id)
                ->first();
            
            $offerData['already_claimed'] = !!$claim;
            $offerData['claim_status'] = $claim?->status;
            
            // Get personalized savings estimate
            $profile = $this->behaviorAnalyzer->getUserBehaviorProfile($user);
            $avgOrderValue = $profile['value_metrics']['average_order_value'] ?? $offer->min_purchase;
            
            $estimatedSavings = min(
                ($avgOrderValue * $offer->discount) / 100,
                $offer->max_discount ?? 999
            );
            
            $offerData['estimated_savings'] = round($estimatedSavings, 2);
            $offerData['recommended_for_you'] = $this->isRecommendedForUser($offer, $profile);
        }

        return response()->json([
            'success' => true,
            'offer' => $offerData,
        ]);
    }

    /**
     * Rank offers by relevance to user
     */
    protected function rankOffersByRelevance($offers, $profile)
    {
        return $offers->map(function($offer) use ($profile) {
            $relevanceScore = 0;
            
            // Higher discount for high churn risk users
            if ($profile['churn_risk'] === 'high' && $offer->discount >= 20) {
                $relevanceScore += 30;
            }
            
            // Match offer type to user tier
            if ($offer->type === 'loyalty' && $profile['value_metrics']['high_value_customer']) {
                $relevanceScore += 25;
            }
            
            // New customer offers for new users
            if ($offer->target_audience === 'new_customers' && $profile['purchase_patterns']['is_new_customer']) {
                $relevanceScore += 35;
            }
            
            // Match min_purchase to typical order value
            $avgOrder = $profile['value_metrics']['average_order_value'] ?? 0;
            if ($avgOrder >= $offer->min_purchase) {
                $relevanceScore += 20;
            }
            
            // Expiring soon gets priority
            $hoursUntilExpiry = now()->diffInHours($offer->valid_until);
            if ($hoursUntilExpiry <= 24) {
                $relevanceScore += 15;
            }
            
            $offer->relevance_score = $relevanceScore;
            return $offer;
        })->sortByDesc('relevance_score');
    }

    /**
     * Check if offer is recommended for user
     */
    protected function isRecommendedForUser(Offer $offer, array $profile): bool
    {
        $recommendations = $profile['recommendations'] ?? [];
        
        foreach ($recommendations as $rec) {
            if ($rec['type'] === $offer->type) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Claim an offer (Phase 1)
     */
    public function claim(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $request->validate([
            'offer_code' => 'required|string',
        ]);

        // Find offer by code
        $offer = Offer::where('code', $request->offer_code)
            ->where('is_active', true)
            ->where('valid_until', '>=', now())
            ->first();

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found or expired'
            ], 404);
        }

        // Check if already claimed
        $existingClaim = OfferClaim::where('user_id', $user->id)
            ->where('offer_id', $offer->id)
            ->first();

        if ($existingClaim) {
            return response()->json([
                'success' => false,
                'message' => 'You have already claimed this offer'
            ], 400);
        }

        // Create claim
        $claim = OfferClaim::create([
            'user_id' => $user->id,
            'offer_id' => $offer->id,
            'claimed_at' => now(),
            'status' => 'active',
            'expires_at' => $offer->valid_until,
        ]);

        // Track analytics
        $this->analyticsService->trackAction($offer, $user, 'claimed', [
            'device_info' => $request->header('User-Agent'),
            'session_data' => ['source' => $request->input('source', 'app')],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offer claimed successfully!',
            'claim' => $claim,
        ], 200);
    }

    /**
     * Get user's claimed offers (Phase 1)
     */
    public function myOffers(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $claims = OfferClaim::with('offer')
            ->where('user_id', $user->id)
            ->orderBy('claimed_at', 'desc')
            ->get()
            ->map(function($claim) {
                return [
                    'id' => $claim->id,
                    'offer_id' => $claim->offer_id,
                    'offer_code' => $claim->offer->code,
                    'title' => $claim->offer->title,
                    'description' => $claim->offer->description,
                    'discount' => $claim->offer->discount,
                    'min_purchase' => $claim->offer->min_purchase,
                    'max_discount' => $claim->offer->max_discount,
                    'claimed_at' => $claim->claimed_at,
                    'expires_at' => $claim->expires_at,
                    'used_at' => $claim->used_at,
                    'status' => $claim->status,
                ];
            });

        return response()->json([
            'success' => true,
            'offers' => $claims,
        ]);
    }

    /**
     * Apply an offer to cart (Phase 1)
     */
    public function apply(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        $request->validate([
            'offer_code' => 'required|string',
            'cart_total' => 'required|numeric|min:0',
        ]);

        // Find claimed offer
        $claim = OfferClaim::with('offer')
            ->where('user_id', $user->id)
            ->whereHas('offer', function($q) use ($request) {
                $q->where('code', $request->offer_code);
            })
            ->where('status', 'active')
            ->first();

        if (!$claim) {
            return response()->json([
                'success' => false,
                'message' => 'Offer not found in your claimed offers'
            ], 404);
        }

        $offer = $claim->offer;

        // Validate min purchase
        if ($request->cart_total < $offer->min_purchase) {
            return response()->json([
                'success' => false,
                'message' => "Minimum purchase of NPR {$offer->min_purchase} required"
            ], 400);
        }

        // Calculate discount
        $discountAmount = ($request->cart_total * $offer->discount) / 100;
        if ($offer->max_discount) {
            $discountAmount = min($discountAmount, $offer->max_discount);
        }

        // Track analytics
        $this->analyticsService->trackAction($offer, $user, 'applied', [
            'cart_total' => $request->cart_total,
            'discount_value' => $discountAmount,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Offer applied successfully!',
            'discount_amount' => round($discountAmount, 2),
            'new_total' => round($request->cart_total - $discountAmount, 2),
        ]);
    }

    /**
     * Remove applied offer (Phase 1)
     */
    public function remove(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        // Simply return success - offer removal is handled client-side
        return response()->json([
            'success' => true,
            'message' => 'Offer removed',
        ]);
    }
}

