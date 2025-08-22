<?php

namespace App\Services;

use App\Models\User;
use App\Models\BadgeClass;
use App\Models\BadgeRank;
use App\Models\BadgeTier;
use App\Models\UserBadge;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SeasonalBadgeService
{
    /**
     * Create a seasonal badge for the current month
     */
    public function createMonthlyBadge($month = null)
    {
        $month = $month ?? now()->format('F Y');
        $badgeCode = strtolower(str_replace(' ', '_', $month)) . '_champion';
        
        // Create seasonal badge class
        $badgeClass = BadgeClass::updateOrCreate(
            ['code' => $badgeCode],
            [
                'name' => "{$month} Champion",
                'description' => "Special badge for {$month} achievements",
                'icon' => $this->getMonthlyIcon($month),
                'is_public' => true,
                'is_active' => true,
                'is_seasonal' => true,
                'expires_at' => now()->addMonth(),
                'requirements' => [
                    ['description' => "Complete {$month} challenges"]
                ],
                'benefits' => [
                    ['description' => 'Exclusive seasonal rewards'],
                    ['description' => 'Limited time privileges'],
                    ['description' => 'Special recognition']
                ]
            ]
        );

        // Create seasonal rank
        $badgeRank = BadgeRank::updateOrCreate(
            ['badge_class_id' => $badgeClass->id, 'code' => 'seasonal'],
            [
                'name' => 'Seasonal',
                'level' => 1,
                'description' => "{$month} achievement",
                'color' => $this->getMonthlyColor($month),
                'is_active' => true
            ]
        );

        // Create seasonal tier
        $badgeTier = BadgeTier::updateOrCreate(
            ['badge_rank_id' => $badgeRank->id, 'level' => 1],
            [
                'name' => 'Champion',
                'description' => "{$month} champion status",
                'points_required' => $this->getSeasonalPoints($month),
                'is_active' => true,
                'benefits' => [
                    ['description' => 'Exclusive seasonal rewards'],
                    ['description' => 'Limited time privileges'],
                    ['description' => 'Special recognition']
                ]
            ]
        );

        return $badgeTier;
    }

    /**
     * Get monthly icon based on month
     */
    private function getMonthlyIcon($month)
    {
        $icons = [
            'January' => '❄️',
            'February' => '💝',
            'March' => '🌸',
            'April' => '🌧️',
            'May' => '🌺',
            'June' => '☀️',
            'July' => '🏖️',
            'August' => '🌻',
            'September' => '🍂',
            'October' => '🎃',
            'November' => '🦃',
            'December' => '🎄'
        ];

        $monthName = explode(' ', $month)[0];
        return $icons[$monthName] ?? '🏆';
    }

    /**
     * Get monthly color based on month
     */
    private function getMonthlyColor($month)
    {
        $colors = [
            'January' => '#87CEEB',
            'February' => '#FF69B4',
            'March' => '#98FB98',
            'April' => '#DDA0DD',
            'May' => '#F0E68C',
            'June' => '#FFD700',
            'July' => '#FF6347',
            'August' => '#FFA500',
            'September' => '#8B4513',
            'October' => '#FF4500',
            'November' => '#8B0000',
            'December' => '#006400'
        ];

        $monthName = explode(' ', $month)[0];
        return $colors[$monthName] ?? '#9370DB';
    }

    /**
     * Get seasonal points requirement
     */
    private function getSeasonalPoints($month)
    {
        // Different challenges for different months
        $challenges = [
            'January' => 500,  // New Year challenges
            'February' => 400, // Valentine's challenges
            'March' => 450,    // Spring challenges
            'April' => 500,    // Easter challenges
            'May' => 550,      // Mother's Day challenges
            'June' => 600,     // Summer challenges
            'July' => 650,     // Independence Day challenges
            'August' => 700,   // Back to school challenges
            'September' => 600, // Fall challenges
            'October' => 550,  // Halloween challenges
            'November' => 500, // Thanksgiving challenges
            'December' => 800  // Holiday challenges
        ];

        $monthName = explode(' ', $month)[0];
        return $challenges[$monthName] ?? 500;
    }

    /**
     * Award seasonal badge to user
     */
    public function awardSeasonalBadge(User $user, $badgeCode)
    {
        $badgeClass = BadgeClass::where('code', $badgeCode)->first();
        if (!$badgeClass) return false;

        $badgeRank = BadgeRank::where('badge_class_id', $badgeClass->id)->first();
        if (!$badgeRank) return false;

        $badgeTier = BadgeTier::where('badge_rank_id', $badgeRank->id)->first();
        if (!$badgeTier) return false;

        // Check if user already has this seasonal badge
        $existingBadge = UserBadge::where('user_id', $user->id)
            ->where('badge_class_id', $badgeClass->id)
            ->first();

        if ($existingBadge) return false;

        // Award the badge
        UserBadge::create([
            'user_id' => $user->id,
            'badge_class_id' => $badgeClass->id,
            'badge_rank_id' => $badgeRank->id,
            'badge_tier_id' => $badgeTier->id,
            'status' => 'active',
            'earned_at' => now(),
            'expires_at' => $badgeClass->expires_at,
            'earned_data' => [
                'seasonal' => true,
                'month' => now()->format('F Y')
            ]
        ]);

        Log::info('Seasonal badge awarded', [
            'user_id' => $user->id,
            'badge_code' => $badgeCode,
            'month' => now()->format('F Y')
        ]);

        return true;
    }

    /**
     * Clean up expired seasonal badges
     */
    public function cleanupExpiredBadges()
    {
        $expiredBadges = BadgeClass::where('is_seasonal', true)
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredBadges as $badgeClass) {
            // Deactivate expired badges
            UserBadge::where('badge_class_id', $badgeClass->id)
                ->update(['status' => 'expired']);

            // Deactivate the badge class
            $badgeClass->update(['is_active' => false]);

            Log::info('Expired seasonal badge cleaned up', [
                'badge_code' => $badgeClass->code,
                'expired_at' => $badgeClass->expires_at
            ]);
        }
    }

    /**
     * Get current seasonal challenges
     */
    public function getCurrentSeasonalChallenges()
    {
        $currentMonth = now()->format('F Y');
        $badgeCode = strtolower(str_replace(' ', '_', $currentMonth)) . '_champion';
        
        $badgeClass = BadgeClass::where('code', $badgeCode)->first();
        
        if (!$badgeClass) {
            // Create current month badge if it doesn't exist
            $this->createMonthlyBadge($currentMonth);
            $badgeClass = BadgeClass::where('code', $badgeCode)->first();
        }

        return [
            'month' => $currentMonth,
            'badge_code' => $badgeCode,
            'points_required' => $this->getSeasonalPoints($currentMonth),
            'icon' => $this->getMonthlyIcon($currentMonth),
            'color' => $this->getMonthlyColor($currentMonth),
            'expires_at' => $badgeClass->expires_at ?? now()->addMonth()
        ];
    }
} 