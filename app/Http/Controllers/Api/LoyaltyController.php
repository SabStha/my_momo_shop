<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LoyaltyController extends Controller
{
    /**
     * Get loyalty summary for the authenticated user.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Get real wallet balance if wallet exists
        $credits = 0;
        try {
            if (\Schema::hasTable('credits_accounts')) {
                $wallet = \DB::table('credits_accounts')
                    ->where('user_id', $user->id)
                    ->first();
                $credits = $wallet ? (int) $wallet->credits_balance : 0;
            }
        } catch (\Exception $e) {
            \Log::info('Wallet table check failed: ' . $e->getMessage());
        }
        
        // Get real badges if badges table exists
        $badges = [];
        try {
            if (\Schema::hasTable('user_badges')) {
                $badges = \DB::table('user_badges')
                    ->join('badges', 'user_badges.badge_id', '=', 'badges.id')
                    ->where('user_badges.user_id', $user->id)
                    ->select('badges.id', 'badges.name', 'badges.tier')
                    ->get()
                    ->toArray();
            }
        } catch (\Exception $e) {
            \Log::info('Badges table check failed: ' . $e->getMessage());
        }
        
        // Determine tier based on credits
        $tier = 'Bronze';
        if ($credits >= 5000) {
            $tier = 'Platinum';
        } elseif ($credits >= 2500) {
            $tier = 'Gold';
        } elseif ($credits >= 1000) {
            $tier = 'Silver';
        }
        
        return response()->json([
            'credits' => $credits,
            'tier' => $tier,
            'badges' => $badges,
        ]);
    }
}
