<?php

namespace App\Services;

use App\Models\User;
use App\Models\BadgeClass;
use App\Models\BadgeProgress;
use App\Models\UserBadge;
use App\Models\Order;
use Carbon\Carbon;

class BadgeProgressionService
{
    /**
     * Process badge progression for a user based on their activities
     */
    public function processUserProgression(User $user)
    {
        $this->processLoyaltyProgression($user);
        $this->processEngagementProgression($user);
        $this->checkForGoldPlusEligibility($user);
    }

    /**
     * Process loyalty progression based on order volume and consistency
     */
    private function processLoyaltyProgression(User $user)
    {
        $loyaltyClass = BadgeClass::where('code', 'loyalty')->first();
        if (!$loyaltyClass) return;

        $progress = $this->getOrCreateProgress($user, $loyaltyClass);
        
        // Calculate loyalty points based on order history
        $loyaltyPoints = $this->calculateLoyaltyPoints($user);
        
        // Update progress
        $progress->addPoints($loyaltyPoints, 'loyalty_calculation', [
            'calculation_date' => now()->toISOString(),
            'orders_count' => $user->orders()->count(),
            'total_spent' => $user->orders()->sum('total'),
            'consistency_score' => $this->calculateConsistencyScore($user)
        ]);

        // Check for new badges
        $this->awardLoyaltyBadges($user, $progress);
    }

    /**
     * Process engagement progression based on engagement activities
     */
    private function processEngagementProgression(User $user)
    {
        $engagementClass = BadgeClass::where('code', 'engagement')->first();
        if (!$engagementClass) return;

        $progress = $this->getOrCreateProgress($user, $engagementClass);
        
        // Calculate engagement points based on various activities
        $engagementPoints = $this->calculateEngagementPoints($user);
        
        // Update progress
        $progress->addPoints($engagementPoints, 'engagement_calculation', [
            'calculation_date' => now()->toISOString(),
            'unique_items_tried' => $this->getUniqueItemsTried($user),
            'referrals_count' => $user->referrals()->count(),
            'social_shares' => $this->getSocialSharesCount($user),
            'donations_made' => $this->getDonationsCount($user)
        ]);

        // Check for new badges
        $this->awardEngagementBadges($user, $progress);
    }

    /**
     * Calculate loyalty points based on order history
     */
    private function calculateLoyaltyPoints(User $user): int
    {
        $orders = $user->orders()->where('status', 'completed')->get();
        
        if ($orders->isEmpty()) return 0;

        $totalSpent = $orders->sum('total');
        $orderCount = $orders->count();
        $consistencyScore = $this->calculateConsistencyScore($user);
        
        // Base points from total spent (1 point per Rs. 10)
        $spendingPoints = (int) ($totalSpent / 10);
        
        // Bonus points for order count
        $orderBonus = $orderCount * 10;
        
        // Consistency bonus
        $consistencyBonus = (int) ($consistencyScore * 100);
        
        return $spendingPoints + $orderBonus + $consistencyBonus;
    }

    /**
     * Calculate engagement points based on various activities
     */
    private function calculateEngagementPoints(User $user): int
    {
        $points = 0;
        
        // Points for trying unique items
        $uniqueItems = $this->getUniqueItemsTried($user);
        $points += $uniqueItems * 50;
        
        // Points for referrals
        $referrals = $user->referrals()->count();
        $points += $referrals * 200;
        
        // Points for social shares (from task completions)
        $socialShares = $user->taskCompletions()
            ->whereHas('creditTask', function ($q) {
                $q->where('code', 'social_share');
            })
            ->count();
        $points += $socialShares * 100;
        
        // Points for donations (from task completions)
        $donations = $user->taskCompletions()
            ->whereHas('creditTask', function ($q) {
                $q->where('code', 'dog_rescue_donation');
            })
            ->count();
        $points += $donations * 500;
        
        // Points for community participation (attending events, etc.)
        $communityPoints = $this->calculateCommunityParticipation($user);
        $points += $communityPoints;
        
        return $points;
    }

    /**
     * Calculate consistency score based on ordering patterns
     */
    private function calculateConsistencyScore(User $user): float
    {
        $orders = $user->orders()
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get();

        if ($orders->count() < 2) return 0;

        $totalDays = $orders->first()->created_at->diffInDays($orders->last());
        if ($totalDays === 0) return 1;

        $orderFrequency = $orders->count() / $totalDays;
        
        // Normalize to a 0-1 scale
        return min(1, $orderFrequency * 7); // Weekly frequency
    }

    /**
     * Get count of unique items tried by user
     */
    private function getUniqueItemsTried(User $user): int
    {
        return $user->orders()
            ->where('status', 'completed')
            ->with('items.product')
            ->get()
            ->flatMap(function ($order) {
                return $order->items->pluck('product.id');
            })
            ->unique()
            ->count();
    }

    /**
     * Get social shares count from task completions
     */
    private function getSocialSharesCount(User $user): int
    {
        return $user->taskCompletions()
            ->whereHas('creditTask', function ($q) {
                $q->where('code', 'social_share');
            })
            ->count();
    }

    /**
     * Get donations count from task completions
     */
    private function getDonationsCount(User $user): int
    {
        return $user->taskCompletions()
            ->whereHas('creditTask', function ($q) {
                $q->where('code', 'dog_rescue_donation');
            })
            ->count();
    }

    /**
     * Calculate community participation points
     */
    private function calculateCommunityParticipation(User $user): int
    {
        // This could be expanded based on actual community features
        // For now, return points based on task completions
        $communityTasks = $user->taskCompletions()
            ->whereHas('creditTask', function ($q) {
                $q->whereIn('code', ['social_share', 'dog_rescue_donation']);
            })
            ->count();
        
        return $communityTasks * 50;
    }

    /**
     * Award loyalty badges based on progress
     */
    private function awardLoyaltyBadges(User $user, BadgeProgress $progress)
    {
        $loyaltyClass = BadgeClass::where('code', 'loyalty')->first();
        $tiers = $loyaltyClass->ranks()
            ->with('tiers')
            ->get()
            ->flatMap(function ($rank) {
                return $rank->tiers;
            })
            ->sortBy('points_required');

        foreach ($tiers as $tier) {
            if ($progress->current_points >= $tier->points_required) {
                $this->awardBadge($user, $tier, 'loyalty_progression');
            }
        }
    }

    /**
     * Award engagement badges based on progress
     */
    private function awardEngagementBadges(User $user, BadgeProgress $progress)
    {
        $engagementClass = BadgeClass::where('code', 'engagement')->first();
        $tiers = $engagementClass->ranks()
            ->with('tiers')
            ->get()
            ->flatMap(function ($rank) {
                return $rank->tiers;
            })
            ->sortBy('points_required');

        foreach ($tiers as $tier) {
            if ($progress->current_points >= $tier->points_required) {
                $this->awardBadge($user, $tier, 'engagement_progression');
            }
        }
    }

    /**
     * Award a badge to a user
     */
    private function awardBadge(User $user, $tier, string $source)
    {
        // Check if user already has this badge
        $existingBadge = $user->userBadges()
            ->where('badge_tier_id', $tier->id)
            ->first();

        if ($existingBadge) return;

        // Create new badge
        $user->userBadges()->create([
            'badge_tier_id' => $tier->id,
            'badge_rank_id' => $tier->badgeRank->id,
            'badge_class_id' => $tier->badgeRank->badgeClass->id,
            'status' => 'active',
            'earned_at' => now(),
            'earned_data' => [
                'source' => $source,
                'points_at_award' => $user->badgeProgress()
                    ->where('badge_class_id', $tier->badgeRank->badgeClass->id)
                    ->first()?->current_points ?? 0
            ]
        ]);

        // Award AmaKo credits for new badge
        $creditsAwarded = $this->calculateBadgeCredits($tier);
        if ($creditsAwarded > 0) {
            $user->addAmaCredits(
                $creditsAwarded,
                "Earned {$tier->badgeRank->name} {$tier->name} badge",
                'badge_earned',
                [
                    'badge_class' => $tier->badgeRank->badgeClass->code,
                    'badge_rank' => $tier->badgeRank->code,
                    'badge_tier' => $tier->level
                ]
            );
        }
    }

    /**
     * Calculate credits to award for earning a badge
     */
    private function calculateBadgeCredits($tier): int
    {
        $baseCredits = 100;
        $rankMultiplier = $tier->badgeRank->level;
        $tierMultiplier = $tier->level;
        
        return $baseCredits * $rankMultiplier * $tierMultiplier;
    }

    /**
     * Check if user is eligible for Gold Plus and award if possible
     */
    private function checkForGoldPlusEligibility(User $user)
    {
        if (!$user->canApplyForGoldPlus()) return;

        $goldPlusClass = BadgeClass::where('code', 'gold_plus')->first();
        $eliteTier = $goldPlusClass->ranks()
            ->with('tiers')
            ->first()
            ->tiers
            ->first();

        // Check if user already has Gold Plus
        $existingBadge = $user->userBadges()
            ->where('badge_class_id', $goldPlusClass->id)
            ->first();

        if ($existingBadge) return;

        // Note: Gold Plus is invite-only, so we don't automatically award it
        // This method could be used to send notifications or create applications
    }

    /**
     * Get or create badge progress for a user and badge class
     */
    private function getOrCreateProgress(User $user, BadgeClass $badgeClass): BadgeProgress
    {
        $progress = $user->badgeProgress()
            ->where('badge_class_id', $badgeClass->id)
            ->first();

        if (!$progress) {
            $progress = $user->badgeProgress()->create([
                'badge_class_id' => $badgeClass->id,
                'current_points' => 0,
                'total_points_earned' => 0,
                'last_activity_at' => now()
            ]);
        }

        return $progress;
    }

    /**
     * Process order completion for badge progression
     */
    public function processOrderCompletion(Order $order)
    {
        $user = $order->user;
        if (!$user) return;

        // Process badge progression
        $this->processUserProgression($user);

        // Check for task completions
        $this->checkOrderTasks($user, $order);
    }

    /**
     * Check for task completions based on order
     */
    private function checkOrderTasks(User $user, Order $order)
    {
        // Check for daily order task
        $dailyOrderTask = \App\Models\CreditTask::where('code', 'daily_order')->first();
        if ($dailyOrderTask && $dailyOrderTask->canBeCompletedByUser($user)) {
            try {
                $user->completeTask('daily_order', [
                    'order_id' => $order->id,
                    'order_total' => $order->total
                ]);
            } catch (\Exception $e) {
                // Task already completed or other error
            }
        }

        // Check for first order task
        $firstOrderTask = \App\Models\CreditTask::where('code', 'first_order')->first();
        if ($firstOrderTask && $firstOrderTask->canBeCompletedByUser($user)) {
            try {
                $user->completeTask('first_order', [
                    'order_id' => $order->id,
                    'order_total' => $order->total
                ]);
            } catch (\Exception $e) {
                // Task already completed or other error
            }
        }

        // Check for try new item task
        $this->checkNewItemTask($user, $order);
    }

    /**
     * Check if order contains new items for the try new item task
     */
    private function checkNewItemTask(User $user, Order $order)
    {
        $tryNewItemTask = \App\Models\CreditTask::where('code', 'try_new_item')->first();
        if (!$tryNewItemTask || !$tryNewItemTask->canBeCompletedByUser($user)) {
            return;
        }

        // Get all products user has ordered before
        $previousProducts = $user->orders()
            ->where('id', '!=', $order->id)
            ->where('status', 'completed')
            ->with('items.product')
            ->get()
            ->flatMap(function ($order) {
                return $order->items->pluck('product.id');
            })
            ->unique();

        // Check if current order has new products
        $currentProducts = $order->items->pluck('product.id')->unique();
        $newProducts = $currentProducts->diff($previousProducts);

        if ($newProducts->isNotEmpty()) {
            try {
                $user->completeTask('try_new_item', [
                    'order_id' => $order->id,
                    'new_products' => $newProducts->toArray()
                ]);
            } catch (\Exception $e) {
                // Task already completed or other error
            }
        }
    }
} 