<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SeasonalBadgeService;
use App\Models\User;
use App\Models\BadgeProgress;

class ManageSeasonalBadges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:seasonal {action : create|cleanup|award} {--user-id= : Specific user ID for award action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage seasonal badges - create, cleanup, or award';

    protected $seasonalService;

    public function __construct(SeasonalBadgeService $seasonalService)
    {
        parent::__construct();
        $this->seasonalService = $seasonalService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'create':
                $this->createCurrentMonthBadge();
                break;
            case 'cleanup':
                $this->cleanupExpiredBadges();
                break;
            case 'award':
                $this->awardSeasonalBadges();
                break;
            default:
                $this->error('Invalid action. Use: create, cleanup, or award');
                return 1;
        }

        return 0;
    }

    private function createCurrentMonthBadge()
    {
        $this->info('Creating current month seasonal badge...');
        
        $badgeTier = $this->seasonalService->createMonthlyBadge();
        
        $this->info("✅ Created seasonal badge: {$badgeTier->badgeRank->badgeClass->name}");
        $this->info("📊 Points required: {$badgeTier->points_required}");
        $this->info("⏰ Expires: {$badgeTier->badgeRank->badgeClass->expires_at}");
    }

    private function cleanupExpiredBadges()
    {
        $this->info('Cleaning up expired seasonal badges...');
        
        $this->seasonalService->cleanupExpiredBadges();
        
        $this->info('✅ Expired seasonal badges cleaned up');
    }

    private function awardSeasonalBadges()
    {
        $userId = $this->option('user-id');
        
        if ($userId) {
            // Award to specific user
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return 1;
            }
            
            $this->awardToUser($user);
        } else {
            // Award to all eligible users
            $this->awardToAllEligibleUsers();
        }
    }

    private function awardToUser(User $user)
    {
        $currentMonth = now()->format('F Y');
        $badgeCode = strtolower(str_replace(' ', '_', $currentMonth)) . '_champion';
        
        // Check if user has enough points for seasonal badge
        $pointsRequired = $this->seasonalService->getSeasonalPoints($currentMonth);
        $userPoints = $user->badgeProgress()
            ->whereHas('badgeClass', function ($q) {
                $q->whereIn('code', ['loyalty', 'engagement']);
            })
            ->sum('current_points');
        
        if ($userPoints >= $pointsRequired) {
            $awarded = $this->seasonalService->awardSeasonalBadge($user, $badgeCode);
            
            if ($awarded) {
                $this->info("✅ Awarded {$currentMonth} Champion badge to {$user->name}");
            } else {
                $this->info("ℹ️ User {$user->name} already has {$currentMonth} Champion badge");
            }
        } else {
            $this->info("ℹ️ User {$user->name} needs {$pointsRequired} points (has {$userPoints})");
        }
    }

    private function awardToAllEligibleUsers()
    {
        $currentMonth = now()->format('F Y');
        $badgeCode = strtolower(str_replace(' ', '_', $currentMonth)) . '_champion';
        $pointsRequired = $this->seasonalService->getSeasonalPoints($currentMonth);
        
        $this->info("Awarding {$currentMonth} Champion badges to eligible users...");
        $this->info("Points required: {$pointsRequired}");
        
        $eligibleUsers = User::whereHas('badgeProgress', function ($query) use ($pointsRequired) {
            $query->whereHas('badgeClass', function ($q) {
                $q->whereIn('code', ['loyalty', 'engagement']);
            })->where('current_points', '>=', $pointsRequired);
        })->get();
        
        $awardedCount = 0;
        $alreadyHadCount = 0;
        
        foreach ($eligibleUsers as $user) {
            $awarded = $this->seasonalService->awardSeasonalBadge($user, $badgeCode);
            
            if ($awarded) {
                $awardedCount++;
                $this->line("✅ Awarded to {$user->name}");
            } else {
                $alreadyHadCount++;
                $this->line("ℹ️ {$user->name} already has badge");
            }
        }
        
        $this->info("🎉 Awarded {$awardedCount} new badges");
        $this->info("ℹ️ {$alreadyHadCount} users already had badges");
        $this->info("📊 Total eligible users: " . $eligibleUsers->count());
    }
} 