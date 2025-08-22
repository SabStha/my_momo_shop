<?php

namespace App\Services;

use App\Models\User;
use App\Models\BadgeProgress;
use Illuminate\Support\Facades\Log;

class ImpactTrackingService
{
    /**
     * Calculate dog rescue impact based on user's engagement badge progress
     */
    public function calculateDogRescueImpact(User $user)
    {
        $engagementProgress = $user->badgeProgress()
            ->whereHas('badgeClass', function ($q) {
                $q->where('code', 'engagement');
            })
            ->first();

        if (!$engagementProgress) {
            return [
                'dogs_rescued' => 0,
                'impact_level' => 'starter',
                'next_milestone' => 1,
                'progress_percentage' => 0
            ];
        }

        // Calculate impact based on engagement points
        $points = $engagementProgress->current_points;
        $dogsRescued = (int) ($points / 100); // 100 points = 1 dog rescued
        $impactLevel = $this->getImpactLevel($dogsRescued);
        $nextMilestone = $this->getNextMilestone($dogsRescued);

        return [
            'dogs_rescued' => $dogsRescued,
            'impact_level' => $impactLevel,
            'next_milestone' => $nextMilestone,
            'progress_percentage' => $this->calculateProgressPercentage($points, $nextMilestone * 100),
            'total_points' => $points,
            'impact_message' => $this->getImpactMessage($dogsRescued, $impactLevel)
        ];
    }

    /**
     * Get impact level based on dogs rescued
     */
    private function getImpactLevel(int $dogsRescued): string
    {
        if ($dogsRescued >= 50) return 'hero';
        if ($dogsRescued >= 25) return 'champion';
        if ($dogsRescued >= 10) return 'supporter';
        if ($dogsRescued >= 5) return 'helper';
        if ($dogsRescued >= 1) return 'starter';
        return 'newcomer';
    }

    /**
     * Get next milestone
     */
    private function getNextMilestone(int $dogsRescued): int
    {
        if ($dogsRescued < 1) return 1;
        if ($dogsRescued < 5) return 5;
        if ($dogsRescued < 10) return 10;
        if ($dogsRescued < 25) return 25;
        if ($dogsRescued < 50) return 50;
        return $dogsRescued + 10; // Increment by 10 for higher levels
    }

    /**
     * Calculate progress percentage
     */
    private function calculateProgressPercentage(int $currentPoints, int $nextMilestonePoints): float
    {
        if ($nextMilestonePoints <= 0) return 100;
        return min(100, max(0, ($currentPoints / $nextMilestonePoints) * 100));
    }

    /**
     * Get personalized impact message
     */
    private function getImpactMessage(int $dogsRescued, string $impactLevel): string
    {
        $messages = [
            'newcomer' => 'Start your journey to help rescue dogs!',
            'starter' => "You've helped rescue {$dogsRescued} dog(s)! Every little bit counts.",
            'helper' => "You've helped rescue {$dogsRescued} dogs! You're making a real difference.",
            'supporter' => "You've helped rescue {$dogsRescued} dogs! You're a true supporter of our cause.",
            'champion' => "You've helped rescue {$dogsRescued} dogs! You're a champion for dog rescue.",
            'hero' => "You've helped rescue {$dogsRescued} dogs! You're a hero in our community!"
        ];

        return $messages[$impactLevel] ?? $messages['newcomer'];
    }

    /**
     * Track impact when user completes engagement activities
     */
    public function trackEngagementImpact(User $user, string $activity, int $points)
    {
        $impact = $this->calculateDogRescueImpact($user);
        $previousDogs = $impact['dogs_rescued'];
        
        // Calculate new impact
        $newPoints = $impact['total_points'] + $points;
        $newDogs = (int) ($newPoints / 100);
        
        if ($newDogs > $previousDogs) {
            $dogsRescued = $newDogs - $previousDogs;
            
            Log::info('Dog rescue impact achieved', [
                'user_id' => $user->id,
                'activity' => $activity,
                'points_earned' => $points,
                'dogs_rescued' => $dogsRescued,
                'total_dogs_rescued' => $newDogs
            ]);

            return [
                'success' => true,
                'dogs_rescued' => $dogsRescued,
                'total_dogs_rescued' => $newDogs,
                'message' => "Congratulations! Your {$activity} helped rescue {$dogsRescued} more dog(s)!"
            ];
        }

        return [
            'success' => true,
            'dogs_rescued' => 0,
            'total_dogs_rescued' => $newDogs,
            'message' => "Great job! You're getting closer to rescuing more dogs."
        ];
    }

    /**
     * Get community impact summary
     */
    public function getCommunityImpact()
    {
        $totalEngagementPoints = BadgeProgress::whereHas('badgeClass', function ($q) {
            $q->where('code', 'engagement');
        })->sum('current_points');

        $totalDogsRescued = (int) ($totalEngagementPoints / 100);
        $totalUsers = User::count();
        $activeUsers = User::whereHas('orders', function ($q) {
            $q->where('created_at', '>=', now()->subDays(30));
        })->count();

        return [
            'total_dogs_rescued' => $totalDogsRescued,
            'total_engagement_points' => $totalEngagementPoints,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'average_dogs_per_user' => $totalUsers > 0 ? round($totalDogsRescued / $totalUsers, 2) : 0,
            'community_impact_level' => $this->getImpactLevel($totalDogsRescued)
        ];
    }
} 