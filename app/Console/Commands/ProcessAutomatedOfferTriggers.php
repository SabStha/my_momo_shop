<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutomatedOfferTriggerService;
use Illuminate\Support\Facades\Log;

class ProcessAutomatedOfferTriggers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:process-triggers {--type= : Specific trigger type to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process automated offer triggers and send personalized offers to users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Processing Automated Offer Triggers...');
        $this->info('');

        try {
            $service = app(AutomatedOfferTriggerService::class);
            
            $triggerType = $this->option('type');
            
            if ($triggerType) {
                // Process specific trigger type
                $this->info("📊 Processing trigger type: {$triggerType}");
                $result = $service->processTriggerType($triggerType);
                
                if (!$result['success'] ?? true) {
                    $this->error('❌ Failed: ' . ($result['error'] ?? 'Unknown error'));
                    return 1;
                }
                
                $this->displayTriggerResult($result);
            } else {
                // Process all triggers
                $this->info('📊 Processing all active triggers...');
                $results = $service->processAllTriggers();
                
                foreach ($results as $triggerName => $result) {
                    $this->displayTriggerResult($result);
                }
            }

            $this->info('');
            $this->info('✅ Automated offer processing complete!');
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('Automated trigger processing failed: ' . $e->getMessage());
            return 1;
        }
    }

    protected function displayTriggerResult($result)
    {
        $trigger = $result['trigger'] ?? 'Unknown';
        $eligible = $result['eligible_users'] ?? 0;
        $created = $result['offers_created'] ?? 0;

        $this->info("📧 {$trigger}:");
        $this->info("   Eligible Users: {$eligible}");
        $this->info("   Offers Created: {$created}");
        
        if ($created > 0) {
            $this->info("   ✅ Success!");
        } elseif ($eligible > 0) {
            $this->warn("   ⚠️  No offers created (users may be in cooldown)");
        } else {
            $this->comment("   ℹ️  No eligible users at this time");
        }
        
        $this->info('');
    }
}
