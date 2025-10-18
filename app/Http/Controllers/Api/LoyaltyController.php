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
        $badgeProgress = null;
        try {
            if (\Schema::hasTable('user_badges')) {
                $badgesData = \DB::table('user_badges')
                    ->join('badge_tiers', 'user_badges.badge_tier_id', '=', 'badge_tiers.id')
                    ->join('badge_ranks', 'badge_tiers.badge_rank_id', '=', 'badge_ranks.id')
                    ->join('badge_classes', 'badge_ranks.badge_class_id', '=', 'badge_classes.id')
                    ->where('user_badges.user_id', $user->id)
                    ->where('user_badges.status', 'active')
                    ->select(
                        'user_badges.id as user_badge_id',
                        'badge_classes.name as class_name',
                        'badge_classes.icon as class_icon',
                        'badge_ranks.name as rank_name',
                        'badge_ranks.color as rank_color',
                        'badge_tiers.name as tier_name',
                        'badge_tiers.level as tier_level',
                        'user_badges.earned_at'
                    )
                    ->orderBy('user_badges.earned_at', 'desc')
                    ->get();
                
                // Format badges for frontend
                $badges = $badgesData->map(function($badge) {
                    return [
                        'id' => $badge->user_badge_id,
                        'name' => $badge->class_name . ' - ' . $badge->rank_name,
                        'tier' => $badge->rank_name,
                        'tier_level' => $badge->tier_level,
                        'icon' => $badge->class_icon,
                        'color' => $badge->rank_color,
                        'earned_at' => $badge->earned_at,
                    ];
                })->toArray();
            }
            
            // Get badge progress for points display
            if (\Schema::hasTable('badge_progress')) {
                $badgeProgress = \DB::table('badge_progress')
                    ->join('badge_classes', 'badge_progress.badge_class_id', '=', 'badge_classes.id')
                    ->where('badge_progress.user_id', $user->id)
                    ->select(
                        'badge_classes.name as class_name',
                        'badge_progress.current_points',
                        'badge_progress.total_points_earned'
                    )
                    ->get()
                    ->toArray();
            }
        } catch (\Exception $e) {
            \Log::error('Badges query failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
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
            'badge_progress' => $badgeProgress,
            'total_badges' => count($badges),
        ]);
    }
}
