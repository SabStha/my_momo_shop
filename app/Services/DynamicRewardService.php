<?php

namespace App\Services;

use App\Models\User;
use App\Models\CreditReward;
use Illuminate\Support\Facades\Log;

class DynamicRewardService
{
    /**
     * Spin the wheel for dynamic rewards
     */
    public function spinWheel(User $user, int $creditsCost = 100)
    {
        // Check if user has enough credits
        if ($user->getAmaCreditBalance() < $creditsCost) {
            return [
                'success' => false,
                'message' => 'Insufficient credits'
            ];
        }

        // Deduct credits
        $user->spendAmaCredits($creditsCost, 'Spin the Wheel', 'dynamic_reward');

        // Generate random reward
        $reward = $this->generateRandomReward($user);

        Log::info('User spun the wheel', [
            'user_id' => $user->id,
            'credits_spent' => $creditsCost,
            'reward' => $reward
        ]);

        return [
            'success' => true,
            'reward' => $reward,
            'credits_spent' => $creditsCost
        ];
    }

    /**
     * Generate random reward based on user's badge level
     */
    private function generateRandomReward(User $user)
    {
        $highestBadge = $user->getHighestBadge('loyalty');
        $badgeLevel = $highestBadge ? $highestBadge->badgeRank->level : 1;

        $rewards = [
            // Common rewards (all users)
            [
                'type' => 'credits',
                'value' => rand(50, 200),
                'weight' => 40,
                'description' => 'Credit bonus'
            ],
            [
                'type' => 'discount',
                'value' => 10,
                'weight' => 25,
                'description' => '10% discount on next order'
            ],
            [
                'type' => 'free_item',
                'value' => 'momo',
                'weight' => 20,
                'description' => 'Free momo of your choice'
            ],
            [
                'type' => 'privilege',
                'value' => 'skip_line',
                'weight' => 10,
                'description' => 'Skip the line privilege'
            ],
            [
                'type' => 'mystery_box',
                'value' => 'random',
                'weight' => 5,
                'description' => 'Mystery momo box'
            ]
        ];

        // Add premium rewards for higher badge levels
        if ($badgeLevel >= 2) {
            $rewards[] = [
                'type' => 'credits',
                'value' => rand(200, 500),
                'weight' => 15,
                'description' => 'Premium credit bonus'
            ];
        }

        if ($badgeLevel >= 3) {
            $rewards[] = [
                'type' => 'event_pass',
                'value' => 'community',
                'weight' => 10,
                'description' => 'Community event pass'
            ];
        }

        return $this->selectWeightedReward($rewards);
    }

    /**
     * Select reward based on weights
     */
    private function selectWeightedReward(array $rewards)
    {
        $totalWeight = array_sum(array_column($rewards, 'weight'));
        $random = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($rewards as $reward) {
            $currentWeight += $reward['weight'];
            if ($random <= $currentWeight) {
                return $reward;
            }
        }

        return $rewards[0]; // Fallback
    }

    /**
     * Open mystery box
     */
    public function openMysteryBox(User $user)
    {
        $items = [
            'steam_momo' => 'Steam Momo (Any Variety)',
            'fried_momo' => 'Fried Momo (Any Variety)',
            'drink' => 'Free Drink',
            'dessert' => 'Free Dessert',
            'combo' => 'Momo Combo'
        ];

        $selectedItem = array_rand($items);
        
        return [
            'success' => true,
            'item' => $selectedItem,
            'description' => $items[$selectedItem],
            'validity_days' => 7
        ];
    }
} 