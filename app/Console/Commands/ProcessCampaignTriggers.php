<?php

namespace App\Console\Commands;

use App\Services\CampaignTriggerService;
use Illuminate\Console\Command;

class ProcessCampaignTriggers extends Command
{
    protected $signature = 'campaigns:process-triggers';
    protected $description = 'Process all active campaign triggers';

    protected $campaignTriggerService;

    public function __construct(CampaignTriggerService $campaignTriggerService)
    {
        parent::__construct();
        $this->campaignTriggerService = $campaignTriggerService;
    }

    public function handle()
    {
        $this->info('Starting campaign trigger processing...');
        
        try {
            $this->campaignTriggerService->processTriggers();
            $this->info('Campaign triggers processed successfully.');
        } catch (\Exception $e) {
            $this->error('Error processing campaign triggers: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
} 