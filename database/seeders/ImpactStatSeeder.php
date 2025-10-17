<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ImpactStat;
use App\Models\Branch;
use Carbon\Carbon;

class ImpactStatSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        $branches = Branch::all();

        if ($branches->isEmpty()) {
            $this->command->warn('No branches found. Please seed branches first.');
            return;
        }

        // Create monthly impact stats for the past 6 months
        for ($i = 0; $i < 6; $i++) {
            $periodStart = Carbon::now()->subMonths($i)->startOfMonth();
            $periodEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            foreach ($branches as $branch) {
                // Simulate sales data (random between 50,000 - 300,000)
                $totalSales = rand(50000, 300000);
                $donationPercentage = 2; // 2% of sales
                $donationAmount = $totalSales * ($donationPercentage / 100);
                
                // Calculate plates funded (Rs. 50 per plate)
                $platesFunded = floor($donationAmount / 50);
                
                // Calculate dogs saved (Rs. 1000 per dog)
                $dogsSaved = floor($donationAmount / 1000);
                
                ImpactStat::create([
                    'branch_id' => $branch->id,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'period_type' => 'monthly',
                    'total_sales' => $totalSales,
                    'donation_amount' => $donationAmount,
                    'donation_percentage' => $donationPercentage,
                    'plates_funded' => $platesFunded,
                    'dogs_saved' => $dogsSaved,
                    'notes' => 'Automated impact tracking for ' . $periodStart->format('F Y'),
                ]);
            }
        }

        // Create current month stats with higher values
        $currentStart = Carbon::now()->startOfMonth();
        $currentEnd = Carbon::now()->endOfMonth();
        
        foreach ($branches as $branch) {
            $totalSales = rand(100000, 400000);
            $donationPercentage = 2;
            $donationAmount = $totalSales * ($donationPercentage / 100);
            $platesFunded = floor($donationAmount / 50);
            $dogsSaved = floor($donationAmount / 1000);
            
            ImpactStat::create([
                'branch_id' => $branch->id,
                'period_start' => $currentStart,
                'period_end' => $currentEnd,
                'period_type' => 'monthly',
                'total_sales' => $totalSales,
                'donation_amount' => $donationAmount,
                'donation_percentage' => $donationPercentage,
                'plates_funded' => $platesFunded,
                'dogs_saved' => $dogsSaved,
                'notes' => 'Current month impact - ' . $currentStart->format('F Y'),
            ]);
        }

        $this->command->info('Impact stats seeded successfully!');
    }
}
