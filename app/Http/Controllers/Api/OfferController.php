<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;

class OfferController extends Controller
{
    /**
     * Get offers available to the authenticated user.
     * This includes active general offers, personalized offers for the user,
     * and offers they have already claimed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function myOffers(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // Get active general offers
            $activeOffers = Offer::active()->latest()->take(10)->get();
            
            // Get personalized offers
            $personalizedOffers = Offer::active()
                ->personalized()
                ->forUser($user->id)
                ->latest()
                ->take(5)
                ->get();
            
            // Get user's claimed offers
            $claimedOffers = $user->offerClaims()
                ->with(['offer'])
                ->orderBy('claimed_at', 'desc')
                ->get()
                ->map(function($claim) {
                    $offer = $claim->offer;
                    if ($offer) {
                        $offer->payment_status = $claim->payment_status; // Pass claim context
                        $offer->is_used = $claim->is_used;
                        $offer->claimed_at = $claim->claimed_at;
                    }
                    return $offer;
                })
                ->filter(function($offer) {
                    return $offer !== null;
                });
            
            // Merge all offers
            $allOffers = collect()
                ->merge($personalizedOffers)
                ->merge($claimedOffers)
                ->merge($activeOffers)
                ->unique('id'); // Ensure no duplicates
                
            return response()->json([
                'success' => true,
                'data' => array_values($allOffers->toArray())
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching my offers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch offers.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
