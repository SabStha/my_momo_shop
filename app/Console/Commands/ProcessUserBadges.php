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
                
                // Check badge classes using direct DB query to avoid caching
                $badgeClassCount = \DB::table('badge_classes')->count();
                $this->line("   Badge Classes in DB: {$badgeClassCount}");
                
                if ($badgeClassCount === 0) {
                    $this->error("   ❌ No badge classes found! Run: php artisan db:seed --class=BadgeSystemSeeder");
                    continue;
                }
                
                // Clear model cache
                \App\Models\BadgeClass::clearBootedModels();
                
                // Check if user has AmaCredit
                if (!$user->amaCredit) {
                    $this->warn("   ⚠️ Creating AmaCredit record for user...");
                    \App\Models\AmaCredit::create([
                        'user_id' => $user->id,
                        'current_balance' => 0,
                        'total_earned' => 0,
                        'total_spent' => 0,
                        'weekly_earned' => 0,
                        'weekly_reset_date' => now()->startOfWeek()->addWeek()->toDateString(),
                        'weekly_cap' => 1000,
                        'last_activity_at' => now(),
                    ]);
                    $this->line("   ✅ AmaCredit record created!");
                }
                
                // Process badge progression
                $this->line("   Processing badge progression...");
                $badgeService->processUserProgression($user);
                
                // Show results
                $userBadges = \App\Models\UserBadge::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->count();
                
                $badgeProgress = \App\Models\BadgeProgress::where('user_id', $user->id)->get();
                
                $this->line("   Badges Earned: {$userBadges}");
                
                if ($badgeProgress->isEmpty()) {
                    $this->warn("   ⚠️ No badge progress created. Check logs for errors.");
                } else {
                    foreach ($badgeProgress as $progress) {
                        $badgeClass = \App\Models\BadgeClass::find($progress->badge_class_id);
                        if ($badgeClass) {
                            $this->line("   {$badgeClass->name}: {$progress->current_points} points");
                        }
                    }
                }
                
                $this->info("   ✅ Complete!\n");
                
            } catch (\Exception $e) {
                $this->error("   ❌ Error: " . $e->getMessage());
                $this->error("   File: " . $e->getFile() . ":" . $e->getLine());
                $this->error("   Stack: " . $e->getTraceAsString() . "\n");
            }
        }

        $this->info("🎉 Badge processing complete!");
        return 0;
    }
}

