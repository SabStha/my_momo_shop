<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Check churn risks daily at 9 AM
        $schedule->command('churn:check')->dailyAt('09:00');
        
        // Process campaign triggers every 5 minutes
        $schedule->command('campaigns:process-triggers')->everyFiveMinutes();
        
        // Clean up declined orders older than 3 months (run monthly on the 1st at 2 AM)
        $schedule->command('orders:cleanup-declined')->monthlyOn(1, '02:00');
        
        // Auto-generate branch updates daily at 10 PM
        $schedule->command('updates:generate-branch')->dailyAt('22:00');
        
        // Calculate impact stats on the 1st of each month at 1 AM
        $schedule->command('impact:calculate')->monthlyOn(1, '01:00');
        
        // Send daily AI offers to mobile users at 10 AM
        $schedule->command('offers:send-daily-ai')->dailyAt('10:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\CreateWalletsForAllUsers::class,
    ];
} 