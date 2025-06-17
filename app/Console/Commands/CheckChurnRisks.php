<?php

namespace App\Console\Commands;

use App\Services\ChurnRiskNotificationService;
use Illuminate\Console\Command;

class CheckChurnRisks extends Command
{
    protected $signature = 'churn:check';
    protected $description = 'Check for customer churn risks across all branches';

    public function handle(ChurnRiskNotificationService $service)
    {
        $notifications = $service->checkChurnRisks();
        
        foreach ($notifications as $notification) {
            $this->info("{$notification['title']}: {$notification['message']}");
        }

        return Command::SUCCESS;
    }
} 