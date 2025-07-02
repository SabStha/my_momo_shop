<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\BadgeClass;
use App\Models\BadgeRank;
use App\Models\BadgeTier;
use App\Models\UserBadge;
use App\Models\BadgeProgress;

class AwardInitialBadges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:award-initial {--user-id= : Award to specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Award initial badges to users for testing';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            $users = User::where('id', $userId)->get();
        } else {
            $users = User::all();
        }

        if ($users->isEmpty()) {
            $this->error('No users found!');
            return;
        }

        $this->info("Awarding initial badges to {$users->count()} user(s)...");

        foreach ($users as $user) {
            $this->awardInitialBadgesToUser($user);
        }

        $this->info('Initial badges awarded successfully!');
    }

    private function awardInitialBadgesToUser(User $user)
    {
        $this->info("Processing user: {$user->name} ({$user->email})");

        // Get badge classes
        $loyaltyClass = BadgeClass::where('code', 'loyalty')->first();
        $engagementClass = BadgeClass::where('code', 'engagement')->first();

        if (!$loyaltyClass || !$engagementClass) {
            $this->error('Badge classes not found. Run the BadgeSystemSeeder first.');
            return;
        }

        // Award Bronze Tier 1 badges for both classes
        $this->awardBadge($user, $loyaltyClass, 'bronze', 1, 'Initial loyalty badge');
        $this->awardBadge($user, $engagementClass, 'bronze', 1, 'Initial engagement badge');

        // Create badge progress records
        $this->createBadgeProgress($user, $loyaltyClass, 150);
        $this->createBadgeProgress($user, $engagementClass, 200);

        $this->info("✓ Awarded initial badges to {$user->name}");
    }

    private function awardBadge(User $user, BadgeClass $badgeClass, string $rankCode, int $tierLevel, string $reason)
    {
        $rank = BadgeRank::where('badge_class_id', $badgeClass->id)
            ->where('code', $rankCode)
            ->first();

        if (!$rank) {
            $this->error("Rank {$rankCode} not found for {$badgeClass->name}");
            return;
        }

        $tier = BadgeTier::where('badge_rank_id', $rank->id)
            ->where('level', $tierLevel)
            ->first();

        if (!$tier) {
            $this->error("Tier {$tierLevel} not found for {$rank->name}");
            return;
        }

        // Check if user already has this badge
        $existingBadge = UserBadge::where('user_id', $user->id)
            ->where('badge_tier_id', $tier->id)
            ->first();

        if ($existingBadge) {
            $this->info("User already has {$badgeClass->name} {$rank->name} {$tier->name}");
            return;
        }

        // Create the badge
        UserBadge::create([
            'user_id' => $user->id,
            'badge_tier_id' => $tier->id,
            'badge_rank_id' => $rank->id,
            'badge_class_id' => $badgeClass->id,
            'status' => 'active',
            'earned_at' => now(),
            'earned_data' => [
                'source' => 'initial_award',
                'reason' => $reason,
                'awarded_by' => 'system'
            ]
        ]);

        $this->info("✓ Awarded {$badgeClass->name} {$rank->name} {$tier->name}");
    }

    private function createBadgeProgress(User $user, BadgeClass $badgeClass, int $points)
    {
        $progress = BadgeProgress::where('user_id', $user->id)
            ->where('badge_class_id', $badgeClass->id)
            ->first();

        if (!$progress) {
            BadgeProgress::create([
                'user_id' => $user->id,
                'badge_class_id' => $badgeClass->id,
                'current_points' => $points,
                'total_points_earned' => $points,
                'last_activity_at' => now()
            ]);
        } else {
            $progress->update([
                'current_points' => $points,
                'total_points_earned' => $points,
                'last_activity_at' => now()
            ]);
        }
    }
} 