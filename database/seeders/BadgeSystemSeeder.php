<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BadgeClass;
use App\Models\BadgeRank;
use App\Models\BadgeTier;
use App\Models\CreditTask;
use App\Models\CreditReward;

class BadgeSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createBadgeClasses();
        $this->createBadgeRanks();
        $this->createBadgeTiers();
        $this->createCreditTasks();
        $this->createCreditRewards();
    }

    private function createBadgeClasses()
    {
        // Momo Loyalty - Public badge class
        BadgeClass::updateOrCreate(
            ['code' => 'loyalty'],
            [
                'name' => 'Momo Loyalty',
                'description' => 'Earn badges through consistent ordering and loyalty to AmaKo Momo. Progress based on order volume and consistency.',
                'icon' => '🥟',
                'is_public' => true,
                'is_active' => true,
                'requirements' => [
                    ['description' => 'Place orders regularly'],
                    ['description' => 'Maintain consistent ordering patterns'],
                    ['description' => 'Build order volume over time']
                ],
                'benefits' => [
                    ['description' => 'Loyalty discounts'],
                    ['description' => 'Priority customer service'],
                    ['description' => 'Special loyalty rewards']
                ]
            ]
        );

        // Momo Engagement - Public badge class
        BadgeClass::updateOrCreate(
            ['code' => 'engagement'],
            [
                'name' => 'Momo Engagement',
                'description' => 'Earn badges through active engagement with the AmaKo community. Progress based on trying different items, referrals, and community participation.',
                'icon' => '🎯',
                'is_public' => true,
                'is_active' => true,
                'requirements' => [
                    ['description' => 'Try different menu items'],
                    ['description' => 'Refer new customers'],
                    ['description' => 'Participate in community events'],
                    ['description' => 'Donate to dog rescue campaigns']
                ],
                'benefits' => [
                    ['description' => 'Exclusive engagement rewards'],
                    ['description' => 'Community recognition'],
                    ['description' => 'Special event invitations']
                ]
            ]
        );
    }

    private function createBadgeRanks()
    {
        $badgeClasses = BadgeClass::all();

        foreach ($badgeClasses as $badgeClass) {
            // Bronze, Silver, Gold, Prestige for all badge classes
            $ranks = [
                [
                    'name' => 'Bronze',
                    'code' => 'bronze',
                    'level' => 1,
                    'description' => 'Beginner level - starting your journey',
                    'color' => '#CD7F32'
                ],
                [
                    'name' => 'Silver',
                    'code' => 'silver',
                    'level' => 2,
                    'description' => 'Intermediate level - growing stronger',
                    'color' => '#C0C0C0'
                ],
                [
                    'name' => 'Gold',
                    'code' => 'gold',
                    'level' => 3,
                    'description' => 'Advanced level - reaching excellence',
                    'color' => '#FFD700'
                ],
                [
                    'name' => 'Prestige',
                    'code' => 'prestige',
                    'level' => 4,
                    'description' => 'Legendary status - ultimate achievement',
                    'color' => '#9370DB'
                ]
            ];

            foreach ($ranks as $rank) {
                BadgeRank::updateOrCreate(
                    ['badge_class_id' => $badgeClass->id, 'code' => $rank['code']],
                    [
                        'name' => $rank['name'],
                        'level' => $rank['level'],
                        'description' => $rank['description'],
                        'color' => $rank['color'],
                        'is_active' => true
                    ]
                );
            }
        }
    }

    private function createBadgeTiers()
    {
        $badgeRanks = BadgeRank::all();

        foreach ($badgeRanks as $rank) {
            // Balanced tier progression for all ranks (including Prestige)
            $tiers = [
                [
                    'name' => 'Tier 1',
                    'level' => 1,
                    'description' => 'First tier - building foundation',
                    'points_required' => $this->getTierPoints($rank->level, 1)
                ],
                [
                    'name' => 'Tier 2',
                    'level' => 2,
                    'description' => 'Second tier - advancing skills',
                    'points_required' => $this->getTierPoints($rank->level, 2)
                ],
                [
                    'name' => 'Tier 3',
                    'level' => 3,
                    'description' => 'Third tier - mastering the rank',
                    'points_required' => $this->getTierPoints($rank->level, 3)
                ]
            ];

            foreach ($tiers as $tier) {
                BadgeTier::updateOrCreate(
                    ['badge_rank_id' => $rank->id, 'level' => $tier['level']],
                    [
                        'name' => $tier['name'],
                        'description' => $tier['description'],
                        'points_required' => $tier['points_required'],
                        'benefits' => $this->getTierBenefits($rank->code, $tier['level']),
                        'is_active' => true
                    ]
                );
            }
        }
    }

    /**
     * Get tier points based on rank level and tier level
     */
    private function getTierPoints($rankLevel, $tierLevel)
    {
        $basePoints = [
            1 => [100, 250, 500],    // Bronze
            2 => [300, 750, 1500],   // Silver
            3 => [600, 1500, 3000],  // Gold
            4 => [1200, 3000, 6000]  // Prestige
        ];

        return $basePoints[$rankLevel][$tierLevel - 1] ?? 1000;
    }

    private function createCreditTasks()
    {
        $loyaltyClass = BadgeClass::where('code', 'loyalty')->first();
        $engagementClass = BadgeClass::where('code', 'engagement')->first();

        // Daily Tasks
        CreditTask::updateOrCreate(
            ['code' => 'daily_order'],
            [
                'name' => 'Daily Order',
                'description' => 'Place an order today',
                'type' => 'daily',
                'credits_reward' => 50,
                'requirements' => [
                    ['description' => 'Place any order today']
                ],
                'validation_rules' => [
                    ['description' => 'Order must be placed today']
                ],
                'is_active' => true
            ]
        );

        CreditTask::updateOrCreate(
            ['code' => 'try_new_item'],
            [
                'name' => 'Try New Item',
                'description' => 'Order a menu item you haven\'t tried before',
                'type' => 'daily',
                'credits_reward' => 75,
                'requirements' => [
                    ['description' => 'Order a new menu item']
                ],
                'validation_rules' => [
                    ['description' => 'Item must not be in previous orders']
                ],
                'is_active' => true
            ]
        );

        // Weekly Tasks
        CreditTask::updateOrCreate(
            ['code' => 'weekly_loyalty'],
            [
                'name' => 'Weekly Loyalty',
                'description' => 'Place 3 orders this week',
                'type' => 'weekly',
                'credits_reward' => 200,
                'requires_badge' => true,
                'required_badge_class_id' => $loyaltyClass->id,
                'requirements' => [
                    ['description' => 'Place 3 orders this week']
                ],
                'validation_rules' => [
                    ['description' => 'Count orders placed this week']
                ],
                'is_active' => true
            ]
        );

        CreditTask::updateOrCreate(
            ['code' => 'social_share'],
            [
                'name' => 'Social Share',
                'description' => 'Share your AmaKo experience on social media',
                'type' => 'weekly',
                'credits_reward' => 150,
                'requires_badge' => true,
                'required_badge_class_id' => $engagementClass->id,
                'requirements' => [
                    ['description' => 'Share AmaKo content on social media']
                ],
                'validation_rules' => [
                    ['description' => 'Manual verification required']
                ],
                'is_active' => true
            ]
        );

        // One-time Tasks
        CreditTask::updateOrCreate(
            ['code' => 'first_order'],
            [
                'name' => 'First Order',
                'description' => 'Complete your first order',
                'type' => 'one_time',
                'credits_reward' => 100,
                'requirements' => [
                    ['description' => 'Place your first order']
                ],
                'validation_rules' => [
                    ['description' => 'Must be first order ever']
                ],
                'is_active' => true
            ]
        );

        CreditTask::updateOrCreate(
            ['code' => 'refer_friend'],
            [
                'name' => 'Refer a Friend',
                'description' => 'Successfully refer a new customer',
                'type' => 'one_time',
                'credits_reward' => 300,
                'requires_badge' => true,
                'required_badge_class_id' => $engagementClass->id,
                'requirements' => [
                    ['description' => 'Refer a new customer who places an order']
                ],
                'validation_rules' => [
                    ['description' => 'Referred user must place first order']
                ],
                'is_active' => true
            ]
        );

        CreditTask::updateOrCreate(
            ['code' => 'dog_rescue_donation'],
            [
                'name' => 'Dog Rescue Donation',
                'description' => 'Make a donation to dog rescue campaign',
                'type' => 'one_time',
                'credits_reward' => 500,
                'requires_badge' => true,
                'required_badge_class_id' => $engagementClass->id,
                'requirements' => [
                    ['description' => 'Donate to dog rescue campaign']
                ],
                'validation_rules' => [
                    ['description' => 'Manual verification required']
                ],
                'is_active' => true
            ]
        );
    }

    private function createCreditRewards()
    {
        $loyaltyClass = BadgeClass::where('code', 'loyalty')->first();
        $engagementClass = BadgeClass::where('code', 'engagement')->first();

        // Free Items
        CreditReward::updateOrCreate(
            ['name' => 'Free Momo (Any Variety)'],
            [
                'description' => 'Get a free momo of your choice',
                'credits_cost' => 200,
                'type' => 'free_item',
                'reward_data' => [
                    'item_type' => 'momo',
                    'max_value' => 150,
                    'validity_days' => 30
                ],
                'is_active' => true
            ]
        );

        CreditReward::updateOrCreate(
            ['name' => 'Free Drink'],
            [
                'description' => 'Get a free drink of your choice',
                'credits_cost' => 150,
                'type' => 'free_item',
                'reward_data' => [
                    'item_type' => 'drink',
                    'max_value' => 100,
                    'validity_days' => 30
                ],
                'is_active' => true
            ]
        );

        // Privileges
        CreditReward::updateOrCreate(
            ['name' => 'Skip the Line'],
            [
                'description' => 'Priority service - skip the queue',
                'credits_cost' => 100,
                'type' => 'privilege',
                'reward_data' => [
                    'privilege_type' => 'priority_service',
                    'validity_days' => 7
                ],
                'is_active' => true
            ]
        );

        CreditReward::updateOrCreate(
            ['name' => 'Tasting Kit'],
            [
                'description' => 'Sample pack of different momo varieties',
                'credits_cost' => 400,
                'type' => 'physical',
                'reward_data' => [
                    'item_type' => 'tasting_kit',
                    'contents' => '5 different momo varieties',
                    'validity_days' => 60
                ],
                'is_active' => true
            ]
        );

        // Badge-specific rewards
        CreditReward::updateOrCreate(
            ['name' => 'Loyalty Discount (10%)'],
            [
                'description' => '10% discount on your next order',
                'credits_cost' => 300,
                'type' => 'discount',
                'reward_data' => [
                    'discount_percentage' => 10,
                    'max_discount' => 200,
                    'validity_days' => 14
                ],
                'requires_badge' => true,
                'required_badge_class_id' => $loyaltyClass->id,
                'is_active' => true
            ]
        );

        CreditReward::updateOrCreate(
            ['name' => 'Community Event Pass'],
            [
                'description' => 'Free entry to next community event',
                'credits_cost' => 500,
                'type' => 'privilege',
                'reward_data' => [
                    'privilege_type' => 'event_access',
                    'event_type' => 'community',
                    'validity_days' => 90
                ],
                'requires_badge' => true,
                'required_badge_class_id' => $engagementClass->id,
                'is_active' => true
            ]
        );
    }

    private function getTierBenefits($rankCode, $tierLevel)
    {
        $benefits = [];
        
        // Get the badge class code from the rank
        $badgeRank = BadgeRank::where('code', $rankCode)->first();
        $badgeClassCode = $badgeRank ? $badgeRank->badgeClass->code : null;
        
        // Base benefits for all tiers
        $baseBenefits = [
            'loyalty' => [
                1 => [
                    ['description' => '5% discount on orders'],
                    ['description' => 'Priority customer support'],
                    ['description' => 'Free delivery on orders above Rs. 500']
                ],
                2 => [
                    ['description' => '10% discount on orders'],
                    ['description' => 'Priority customer support'],
                    ['description' => 'Free delivery on orders above Rs. 300'],
                    ['description' => 'Early access to new menu items']
                ],
                3 => [
                    ['description' => '15% discount on orders'],
                    ['description' => 'VIP customer support'],
                    ['description' => 'Free delivery on all orders'],
                    ['description' => 'Early access to new menu items'],
                    ['description' => 'Exclusive loyalty rewards']
                ]
            ],
            'engagement' => [
                1 => [
                    ['description' => 'Community recognition'],
                    ['description' => 'Access to community events'],
                    ['description' => 'Special engagement rewards']
                ],
                2 => [
                    ['description' => 'Community recognition'],
                    ['description' => 'Priority access to community events'],
                    ['description' => 'Enhanced engagement rewards'],
                    ['description' => 'Exclusive social media features']
                ],
                3 => [
                    ['description' => 'Community leader status'],
                    ['description' => 'VIP access to all community events'],
                    ['description' => 'Premium engagement rewards'],
                    ['description' => 'Exclusive social media features'],
                    ['description' => 'Influence on community decisions']
                ]
            ]
        ];
        
        // Add rank-specific benefits
        $rankBenefits = [
            'bronze' => [
                ['description' => 'Basic tier benefits'],
                ['description' => 'Standard customer service']
            ],
            'silver' => [
                ['description' => 'Enhanced tier benefits'],
                ['description' => 'Priority customer service'],
                ['description' => 'Exclusive silver member perks']
            ],
            'gold' => [
                ['description' => 'Premium tier benefits'],
                ['description' => 'VIP customer service'],
                ['description' => 'Exclusive gold member perks'],
                ['description' => 'Special gold member events']
            ]
        ];
        
        // Combine base benefits with rank benefits
        if ($badgeClassCode && isset($baseBenefits[$badgeClassCode][$tierLevel])) {
            $benefits = array_merge($benefits, $baseBenefits[$badgeClassCode][$tierLevel]);
        }
        
        if (isset($rankBenefits[$rankCode])) {
            $benefits = array_merge($benefits, $rankBenefits[$rankCode]);
        }
        
        return $benefits;
    }
} 