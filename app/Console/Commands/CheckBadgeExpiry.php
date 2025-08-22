<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\UserBadge;
use App\Models\User;
use Carbon\Carbon;

class CheckBadgeExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and handle badge expiry based on user activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking badge expiry...');

        // Check for inactive users (no orders in 90 days)
        $inactiveUsers = User::whereDoesntHave('orders', function ($query) {
            $query->where('created_at', '>=', now()->subDays(90));
        })->get();

        foreach ($inactiveUsers as $user) {
            $this->handleInactiveUser($user);
        }

        // Check for expired badges based on activity
        $this->checkActivityBasedExpiry();

        $this->info('Badge expiry check completed!');
    }

    private function handleInactiveUser(User $user)
    {
        $badges = $user->userBadges()->where('status', 'active')->get();
        
        foreach ($badges as $badge) {
            // Downgrade high-tier badges for inactive users
            if ($badge->badgeRank->level >= 2) { // Silver and Gold badges
                $badge->update([
                    'status' => 'inactive',
                    'expires_at' => now()->addDays(30), // Give 30 days to reactivate
                ]);
                
                $this->info("Downgraded {$badge->badgeRank->name} badge for inactive user {$user->name}");
            }
        }
    }

    private function checkActivityBasedExpiry()
    {
        // Check badges that need revalidation
        $badgesNeedingRevalidation = UserBadge::where('status', 'active')
            ->where('earned_at', '<=', now()->subMonths(6)) // 6 months old
            ->get();

        foreach ($badgesNeedingRevalidation as $badge) {
            $user = $badge->user;
            
            // Check if user has recent activity
            $recentActivity = $user->orders()
                ->where('created_at', '>=', now()->subDays(30))
                ->exists();

            if (!$recentActivity) {
                $badge->update([
                    'status' => 'inactive',
                    'expires_at' => now()->addDays(14), // 14 days to reactivate
                ]);
                
                $this->info("Marked badge for revalidation: {$user->name} - {$badge->badgeRank->name}");
            }
        }
    }
} 