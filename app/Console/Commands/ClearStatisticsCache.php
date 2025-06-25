<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\StatisticsService;

class ClearStatisticsCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the statistics cache to refresh the data';

    /**
     * Execute the console command.
     */
    public function handle(StatisticsService $statisticsService)
    {
        $statisticsService->clearCache();
        
        $this->info('Statistics cache cleared successfully!');
        $this->info('Statistics will be refreshed on the next request.');
        
        return Command::SUCCESS;
    }
} 