<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIOfferService;
use App\Services\MobileNotificationService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendDailyAIOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:send-daily-ai {--branch=1 : Branch ID to generate offers for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send AI-powered daily offers to mobile app users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Starting Daily AI Offer Generation...');
        $this->info('');

        try {
            $branchId = $this->option('branch');
            
            // Generate AI offers
            $this->info("📊 Analyzing business data for branch {$branchId}...");
            $aiOfferService = app(AIOfferService::class);
            $result = $aiOfferService->generateAIOffers($branchId);

            if (!$result['success']) {
                $this->error('❌ Failed to generate AI offers: ' . ($result['error'] ?? 'Unknown error'));
                return 1;
            }

            $offersCount = $result['offers_created'] ?? 0;
            $this->info("✅ Successfully generated {$offersCount} AI offers");
            
            if ($offersCount > 0) {
                $this->info('📱 Notifications sent to mobile users automatically');
                $this->displayOffersSummary($result['offers']);
            } else {
                $this->warn('⚠️  No new offers generated (business conditions may not warrant new offers)');
            }

            $this->info('');
            $this->info('🎉 Daily AI Offer process completed successfully!');
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            Log::error('Daily AI Offers Command Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

    /**
     * Display summary of generated offers
     */
    protected function displayOffersSummary($offers)
    {
        $this->info('');
        $this->info('📋 Generated Offers:');
        $this->info('');

        foreach ($offers as $offer) {
            $this->line("  🎁 {$offer->title}");
            $this->line("     Discount: {$offer->discount}%");
            $this->line("     Code: {$offer->code}");
            $this->line("     Type: {$offer->type}");
            $this->line("     Target: {$offer->target_audience}");
            $this->line("     Valid until: {$offer->valid_until->format('Y-m-d H:i')}");
            $this->line('');
        }
    }
}

