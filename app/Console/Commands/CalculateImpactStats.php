<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;
use App\Models\ImpactStat;
use App\Models\Order;
use Carbon\Carbon;

class CalculateImpactStats extends Command
{
    protected $signature = 'impact:calculate {--month= : Month to calculate (YYYY-MM), defaults to current month}';
    protected $description = 'Calculate monthly impact statistics from sales data';

    // Configuration
    private $donationPercentage = 2; // 2% of sales donated
    private $platesCost = 50; // Rs 50 per plate
    private $dogRescueCost = 1000; // Rs 1000 per dog rescue

    public function handle()
    {
        $month = $this->option('month') ?: now()->format('Y-m');
        $date = Carbon::parse($month . '-01');
        
        $this->info("🤖 Calculating Impact Stats for {$date->format('F Y')}");
        $this->info('');

        $branches = Branch::where('is_active', true)->get();
        $totalImpact = [
            'donation' => 0,
            'plates' => 0,
            'dogs' => 0,
        ];

        foreach ($branches as $branch) {
            $impact = $this->calculateBranchImpact($branch, $date);
            
            if ($impact['sales'] > 0) {
                $this->info("✅ {$branch->name}:");
                $this->info("   Sales: Rs " . number_format($impact['sales'], 2));
                $this->info("   Donation: Rs " . number_format($impact['donation'], 2));
                $this->info("   Plates: {$impact['plates']}");
                $this->info("   Dogs: {$impact['dogs']}");
                $this->info('');

                $totalImpact['donation'] += $impact['donation'];
                $totalImpact['plates'] += $impact['plates'];
                $totalImpact['dogs'] += $impact['dogs'];

                // Save to database
                $this->saveImpactStat($branch, $date, $impact);
            }
        }

        $this->info('📊 TOTAL IMPACT:');
        $this->info("   Total Donation: Rs " . number_format($totalImpact['donation'], 2));
        $this->info("   Total Plates Funded: {$totalImpact['plates']}");
        $this->info("   Total Dogs Saved: {$totalImpact['dogs']}");
        $this->info('');
        $this->info('🎉 Impact stats calculated and saved!');

        return 0;
    }

    private function calculateBranchImpact($branch, $date)
    {
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        // Get total sales for the month
        $totalSales = Order::where('branch_id', $branch->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // Calculate donation
        $donation = $totalSales * ($this->donationPercentage / 100);

        // Calculate impact
        $platesFunded = floor($donation / $this->platesCost);
        $dogsSaved = floor($donation / $this->dogRescueCost);

        return [
            'sales' => $totalSales,
            'donation' => $donation,
            'plates' => $platesFunded,
            'dogs' => $dogsSaved,
        ];
    }

    private function saveImpactStat($branch, $date, $impact)
    {
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        ImpactStat::updateOrCreate(
            [
                'branch_id' => $branch->id,
                'period_start' => $startDate,
                'period_end' => $endDate,
                'period_type' => 'monthly',
            ],
            [
                'total_sales' => $impact['sales'],
                'donation_amount' => $impact['donation'],
                'donation_percentage' => $this->donationPercentage,
                'plates_funded' => $impact['plates'],
                'dogs_saved' => $impact['dogs'],
                'notes' => 'Auto-calculated from sales data for ' . $date->format('F Y'),
            ]
        );
    }
}
