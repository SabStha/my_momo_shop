<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIOfferService;
use App\Models\Branch;
use Illuminate\Support\Facades\Log;

class GenerateAIOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:generate-ai {--branch= : Specific branch ID} {--personalized : Generate personalized offers for users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI-powered offers based on business data';

    protected $aiOfferService;

    /**
     * Create a new command instance.
     */
    public function __construct(AIOfferService $aiOfferService)
    {
        parent::__construct();
        $this->aiOfferService = $aiOfferService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Starting AI Offer Generation...');

        try {
            $branchId = $this->option('branch');
            $personalized = $this->option('personalized');

            if ($personalized) {
                $this->generatePersonalizedOffers($branchId);
            } else {
                $this->generateGeneralOffers($branchId);
            }

            $this->info('✅ AI Offer Generation completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ AI Offer Generation failed: ' . $e->getMessage());
            Log::error('AI Offer Generation Command Failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate general AI offers for all branches or specific branch
     */
    protected function generateGeneralOffers($branchId = null)
    {
        if ($branchId) {
            $this->info("Generating AI offers for branch ID: {$branchId}");
            $result = $this->aiOfferService->generateAIOffers($branchId);
            $this->displayResult($result, $branchId);
        } else {
            $this->info('Generating AI offers for all branches...');
            
            $branches = Branch::all();
            $totalOffers = 0;

            foreach ($branches as $branch) {
                $this->info("Processing branch: {$branch->name} (ID: {$branch->id})");
                
                $result = $this->aiOfferService->generateAIOffers($branch->id);
                $this->displayResult($result, $branch->id);
                
                if ($result['success']) {
                    $totalOffers += $result['offers_created'];
                }
            }

            $this->info("Total offers generated across all branches: {$totalOffers}");
        }
    }

    /**
     * Generate personalized offers for users
     */
    protected function generatePersonalizedOffers($branchId = null)
    {
        $this->info('Generating personalized offers for users...');

        $users = \App\Models\User::whereHas('orders', function($query) use ($branchId) {
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }
        })->get();

        $this->info("Found {$users->count()} users with order history");

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        $totalPersonalizedOffers = 0;

        foreach ($users as $user) {
            try {
                $result = $this->aiOfferService->generatePersonalizedOffers($user, $branchId ?? 1);
                
                if ($result['success']) {
                    $totalPersonalizedOffers += count($result['offers']);
                }
                
                $progressBar->advance();
                
            } catch (\Exception $e) {
                $this->warn("Failed to generate personalized offers for user {$user->id}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Total personalized offers generated: {$totalPersonalizedOffers}");
    }

    /**
     * Display the result of offer generation
     */
    protected function displayResult($result, $branchId)
    {
        if ($result['success']) {
            $this->info("✅ Branch {$branchId}: Generated {$result['offers_created']} offers");
            
            if (isset($result['offers']) && count($result['offers']) > 0) {
                $this->table(
                    ['Title', 'Code', 'Discount', 'Type', 'Target'],
                    collect($result['offers'])->map(function($offer) {
                        return [
                            $offer->title,
                            $offer->code,
                            $offer->discount . '%',
                            ucfirst($offer->type ?? 'discount'),
                            ucfirst(str_replace('_', ' ', $offer->target_audience))
                        ];
                    })->toArray()
                );
            }
        } else {
            $this->error("❌ Branch {$branchId}: Failed - {$result['error']}");
        }
    }
}
