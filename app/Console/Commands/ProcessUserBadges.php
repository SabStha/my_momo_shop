<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\BadgeProgressionService;

class ProcessUserBadges extends Command
{
    protected $signature = 'badges:process {user_id?}';
    protected $description = 'Process badge progression for a user based on their order history';

    public function handle(BadgeProgressionService $badgeService)
    {
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $users = User::where('id', $userId)->get();
            if ($users->isEmpty()) {
                $this->error("User ID {$userId} not found!");
                return 1;
            }
        } else {
            // Process all users with orders
            $users = User::whereHas('orders')->get();
        }

        $this->info("🎯 Processing badges for " . $users->count() . " user(s)...\n");

        foreach ($users as $user) {
            $this->info("👤 Processing: {$user->name} (ID: {$user->id})");
            
            try {
                // Count eligible orders
                $ordersCount = $user->orders()
                    ->whereIn('status', ['completed', 'delivered', 'pending'])
                    ->count();
                
                $this->line("   Orders: {$ordersCount}");
                
                // Process badge progression
                $badgeService->processUserProgression($user);
                
                // Show results
                $userBadges = \App\Models\UserBadge::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->count();
                
                $badgeProgress = \App\Models\BadgeProgress::where('user_id', $user->id)->get();
                
                $this->line("   Badges Earned: {$userBadges}");
                
                foreach ($badgeProgress as $progress) {
                    $badgeClass = \App\Models\BadgeClass::find($progress->badge_class_id);
                    $this->line("   {$badgeClass->name}: {$progress->current_points} points");
                }
                
                $this->info("   ✅ Complete!\n");
                
            } catch (\Exception $e) {
                $this->error("   ❌ Error: " . $e->getMessage());
                $this->error("   Stack: " . $e->getTraceAsString() . "\n");
            }
        }

        $this->info("🎉 Badge processing complete!");
        return 0;
    }
}

