<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Creator;
use App\Models\CreatorReward;
use Carbon\Carbon;

class AssignMonthlyCreatorRewards extends Command
{
    protected $signature = 'creators:assign-monthly-rewards';
    protected $description = 'Assign monthly rewards to creators based on leaderboard badge.';

    public function handle()
    {
        $month = Carbon::now()->subMonth()->format('Y-m'); // For previous month
        $creators = Creator::orderByDesc('points')->get();
        $badges = ['gold', 'silver', 'bronze'];
        $rewards = [
            'gold' => 'Gold Bonus $100',
            'silver' => 'Silver Bonus $50',
            'bronze' => 'Bronze Bonus $25',
        ];
        $badgeCounts = [1, 2, 3]; // Top 1: gold, next 2: silver, next 3: bronze
        $rank = 1;
        foreach ($creators as $i => $creator) {
            $badge = null;
            if ($i == 0) $badge = 'gold';
            elseif ($i < 3) $badge = 'silver';
            elseif ($i < 6) $badge = 'bronze';
            if ($badge) {
                CreatorReward::updateOrCreate([
                    'creator_id' => $creator->id,
                    'month' => $month,
                    'badge' => $badge,
                ], [
                    'reward' => $rewards[$badge],
                ]);
            }
        }
        $this->info('Monthly rewards assigned for ' . $month);
    }
} 