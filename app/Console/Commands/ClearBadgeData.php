<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BadgeClass;
use App\Models\BadgeRank;
use App\Models\BadgeTier;
use App\Models\UserBadge;
use App\Models\BadgeProgress;
use App\Models\CreditTask;
use App\Models\UserTaskCompletion;
use App\Models\CreditReward;
use App\Models\UserRewardRedemption;

class ClearBadgeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all badge-related data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing all badge-related data...');

        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear in reverse order to avoid foreign key constraints
        UserRewardRedemption::truncate();
        $this->info('✓ Cleared user reward redemptions');

        CreditReward::truncate();
        $this->info('✓ Cleared credit rewards');

        UserTaskCompletion::truncate();
        $this->info('✓ Cleared user task completions');

        CreditTask::truncate();
        $this->info('✓ Cleared credit tasks');

        UserBadge::truncate();
        $this->info('✓ Cleared user badges');

        BadgeProgress::truncate();
        $this->info('✓ Cleared badge progress');

        BadgeTier::truncate();
        $this->info('✓ Cleared badge tiers');

        BadgeRank::truncate();
        $this->info('✓ Cleared badge ranks');

        BadgeClass::truncate();
        $this->info('✓ Cleared badge classes');

        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('All badge data cleared successfully!');
    }
} 