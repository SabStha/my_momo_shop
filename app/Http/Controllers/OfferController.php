<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\OfferClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OfferController extends Controller
{
    /**
     * Claim an offer
     */
    public function claim(Request $request)
    {
        try {
            $request->validate([
                'offer_code' => 'required|string|max:50'
            ]);

            $offerCode = $request->input('offer_code');
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to claim offers'
                ], 401);
            }

            // Find the offer
            $offer = Offer::where('code', $offerCode)
                         ->where('is_active', true)
                         ->where('valid_from', '<=', now())
                         ->where('valid_until', '>=', now())
                         ->first();

            if (!$offer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer not found or expired'
                ], 404);
            }

            // Check if user already claimed this offer
            $existingClaim = OfferClaim::where('user_id', $user->id)
                                      ->where('offer_id', $offer->id)
                                      ->first();

            if ($existingClaim) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already claimed this offer'
                ], 400);
            }

            // Check if offer is personalized and user is not the target
            if ($offer->target_audience === 'personalized' && $offer->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'This offer is not available for you'
                ], 403);
            }

            // Create the claim
            $claim = OfferClaim::create([
                'user_id' => $user->id,
                'offer_id' => $offer->id,
                'claimed_at' => now(),
                'status' => 'active'
            ]);

            // Log the claim
            Log::info('Offer claimed', [
                'user_id' => $user->id,
                'offer_id' => $offer->id,
                'offer_code' => $offerCode
            ]);

            return response()->json([
                'success' => true,
                'message' => "Offer '{$offer->title}' claimed successfully!",
                'offer' => [
                    'id' => $offer->id,
                    'title' => $offer->title,
                    'code' => $offer->code,
                    'discount' => $offer->discount,
                    'min_purchase' => $offer->min_purchase,
                    'max_discount' => $offer->max_discount,
                    'valid_until' => $offer->valid_until
                ],
                'claim_id' => $claim->id
            ]);

        } catch (\Exception $e) {
            Log::error('Offer claim failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to claim offer. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user's claimed offers
     */
    public function myClaims()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to view your claims'
                ], 401);
            }

            $claims = OfferClaim::where('user_id', $user->id)
                               ->with(['offer'])
                               ->orderBy('claimed_at', 'desc')
                               ->get();

            return response()->json([
                'success' => true,
                'claims' => $claims->map(function($claim) {
                    return [
                        'id' => $claim->id,
                        'offer' => [
                            'title' => $claim->offer->title,
                            'code' => $claim->offer->code,
                            'discount' => $claim->offer->discount,
                            'description' => $claim->offer->description
                        ],
                        'claimed_at' => $claim->claimed_at,
                        'status' => $claim->status,
                        'used_at' => $claim->used_at,
                        'discount_applied' => $claim->discount_applied
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user claims: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load your claims'
            ], 500);
        }
    }

    /**
     * Apply offer to cart/order
     */
    public function apply(Request $request)
    {
        try {
            $request->validate([
                'claim_id' => 'required|integer|exists:offer_claims,id'
            ]);

            $user = Auth::user();
            $claimId = $request->input('claim_id');

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to apply offers'
                ], 401);
            }

            // Get the claim
            $claim = OfferClaim::where('id', $claimId)
                              ->where('user_id', $user->id)
                              ->where('status', 'active')
                              ->with(['offer'])
                              ->first();

            if (!$claim) {
                return response()->json([
                    'success' => false,
                    'message' => 'Offer claim not found or already used'
                ], 404);
            }

            // Check if offer is still valid
            if ($claim->offer->valid_until < now()) {
                $claim->update(['status' => 'expired']);
                return response()->json([
                    'success' => false,
                    'message' => 'This offer has expired'
                ], 400);
            }

            // Store in session for checkout
            session(['active_offer_claim' => $claim->id]);

            return response()->json([
                'success' => true,
                'message' => "Offer '{$claim->offer->title}' applied to your cart!",
                'offer' => [
                    'id' => $claim->offer->id,
                    'title' => $claim->offer->title,
                    'code' => $claim->offer->code,
                    'discount' => $claim->offer->discount,
                    'min_purchase' => $claim->offer->min_purchase,
                    'max_discount' => $claim->offer->max_discount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to apply offer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply offer'
            ], 500);
        }
    }

    /**
     * Remove applied offer
     */
    public function remove()
    {
        try {
            session()->forget('active_offer_claim');
            
            return response()->json([
                'success' => true,
                'message' => 'Offer removed from cart'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to remove offer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove offer'
            ], 500);
        }
    }

    /**
     * Get available offers for user
     */
    public function available()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to view offers'
                ], 401);
            }

            // Get all active offers
            $offers = Offer::active()
                          ->where(function($query) use ($user) {
                              $query->whereNull('user_id') // General offers
                                    ->orWhere('user_id', $user->id); // Personalized offers
                          })
                          ->get();

            // Filter out already claimed offers
            $claimedOfferIds = OfferClaim::where('user_id', $user->id)
                                        ->pluck('offer_id')
                                        ->toArray();

            $availableOffers = $offers->filter(function($offer) use ($claimedOfferIds) {
                return !in_array($offer->id, $claimedOfferIds);
            });

            return response()->json([
                'success' => true,
                'offers' => $availableOffers->map(function($offer) {
                    return [
                        'id' => $offer->id,
                        'title' => $offer->title,
                        'description' => $offer->description,
                        'code' => $offer->code,
                        'discount' => $offer->discount,
                        'type' => $offer->type,
                        'min_purchase' => $offer->min_purchase,
                        'max_discount' => $offer->max_discount,
                        'valid_until' => $offer->valid_until,
                        'target_audience' => $offer->target_audience,
                        'ai_generated' => $offer->ai_generated
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get available offers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load offers'
            ], 500);
        }
    }
} 