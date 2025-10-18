<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BadgeClass;
use App\Models\BadgeRank;
use App\Models\BadgeTier;

class BadgeSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏆 Seeding Badge System...');

        // Check if badge classes already exist
        $existingCount = \DB::table('badge_classes')->whereNull('deleted_at')->count();
        
        if ($existingCount > 0) {
            $this->command->warn("Badge system already seeded ({$existingCount} classes found). Skipping...");
            $this->command->info('To re-seed, first run: php artisan db:seed:reset --class=BadgeSystemSeeder');
            return;
        }

        // Clear any soft-deleted badge data
        \DB::table('badge_tiers')->delete();
        \DB::table('badge_ranks')->delete();
        \DB::table('badge_classes')->delete();

        // Create Badge Classes
        $loyalty = BadgeClass::create([
            'name' => 'Momo Loyalty',
            'code' => 'loyalty',
            'description' => 'Earn badges through consistent ordering and loyalty',
            'icon' => '🥟',
            'is_public' => true,
            'is_active' => true,
            'requirements' => json_encode([
                'min_orders' => 1,
                'min_total_spent' => 0,
            ]),
            'benefits' => json_encode([
                'loyalty_discounts' => true,
                'priority_support' => true,
                'special_rewards' => true,
            ]),
        ]);

        $engagement = BadgeClass::create([
            'name' => 'Momo Engagement',
            'code' => 'engagement',
            'description' => 'Earn badges through active community engagement',
            'icon' => '🎯',
            'is_public' => true,
            'is_active' => true,
            'requirements' => json_encode([
                'min_activities' => 1,
            ]),
            'benefits' => json_encode([
                'exclusive_rewards' => true,
                'community_recognition' => true,
                'event_invitations' => true,
            ]),
        ]);

        $this->command->info('✅ Badge Classes created: ' . BadgeClass::count());

        // Create Badge Ranks for each class
        $ranks = [
            ['name' => 'Bronze', 'level' => 1, 'color' => '#CD7F32'],
            ['name' => 'Silver', 'level' => 2, 'color' => '#C0C0C0'],
            ['name' => 'Gold', 'level' => 3, 'color' => '#FFD700'],
            ['name' => 'Prestige', 'level' => 4, 'color' => '#9370DB'],
        ];

        foreach ([$loyalty, $engagement] as $badgeClass) {
            foreach ($ranks as $rankData) {
                $rank = BadgeRank::create([
                    'badge_class_id' => $badgeClass->id,
                    'name' => $rankData['name'],
                    'code' => strtolower($rankData['name']),
                    'description' => $this->getRankDescription($rankData['name']),
                    'level' => $rankData['level'],
                    'color' => $rankData['color'],
                    'requirements' => json_encode([
                        'previous_rank_required' => $rankData['level'] > 1,
                        'min_points' => $this->getBasePoints($rankData['level']),
                    ]),
                    'benefits' => json_encode([
                        'icon' => $this->getRankIcon($rankData['name']),
                        'tier_count' => 3,
                    ]),
                    'is_active' => true,
                ]);

                // Create 3 tiers for each rank
                $this->createTiersForRank($rank);
            }
        }

        $this->command->info('✅ Badge Ranks created: ' . BadgeRank::count());
        $this->command->info('✅ Badge Tiers created: ' . BadgeTier::count());
        $this->command->info('🎉 Badge System seeding completed!');
    }

    /**
     * Create 3 tiers for a given rank
     */
    private function createTiersForRank(BadgeRank $rank): void
    {
        $basePoints = $this->getBasePoints($rank->level);

        $tiers = [
            [
                'level' => 1,
                'multiplier' => 1,
                'name' => 'Tier 1',
                'description' => 'Foundation level',
            ],
            [
                'level' => 2,
                'multiplier' => 2.5,
                'name' => 'Tier 2',
                'description' => 'Advancement level',
            ],
            [
                'level' => 3,
                'multiplier' => 5,
                'name' => 'Tier 3',
                'description' => 'Mastery level',
            ],
        ];

        foreach ($tiers as $tierData) {
            BadgeTier::create([
                'badge_rank_id' => $rank->id,
                'level' => $tierData['level'],
                'name' => $tierData['name'],
                'description' => $tierData['description'],
                'points_required' => (int) ($basePoints * $tierData['multiplier']),
                'requirements' => json_encode([
                    'min_points' => (int) ($basePoints * $tierData['multiplier']),
                ]),
                'benefits' => json_encode([
                    'icon' => $this->getTierIcon($tierData['level']),
                    'unlock_message' => "Congratulations! You've unlocked {$rank->name} {$tierData['name']}!",
                ]),
                'is_active' => true,
            ]);
        }
    }

    /**
     * Get base points for each rank level
     */
    private function getBasePoints(int $level): int
    {
        return match ($level) {
            1 => 100,  // Bronze
            2 => 300,  // Silver
            3 => 600,  // Gold
            4 => 1200, // Prestige
            default => 100,
        };
    }

    /**
     * Get rank description
     */
    private function getRankDescription(string $rankName): string
    {
        return match ($rankName) {
            'Bronze' => 'Beginner level - starting your journey',
            'Silver' => 'Intermediate level - growing stronger',
            'Gold' => 'Advanced level - reaching excellence',
            'Prestige' => 'Legendary status - ultimate achievement',
            default => 'Badge rank',
        };
    }

    /**
     * Get rank icon
     */
    private function getRankIcon(string $rankName): string
    {
        return match ($rankName) {
            'Bronze' => '🥉',
            'Silver' => '🥈',
            'Gold' => '🥇',
            'Prestige' => '👑',
            default => '🏅',
        };
    }

    /**
     * Get tier icon
     */
    private function getTierIcon(int $level): string
    {
        return match ($level) {
            1 => '⭐',
            2 => '⭐⭐',
            3 => '⭐⭐⭐',
            default => '⭐',
        };
    }
}

