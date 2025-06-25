<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Investor;
use App\Models\InvestorInvestment;
use App\Models\InvestorPayout;
use App\Models\Order;
use App\Models\Branch;
use Carbon\Carbon;

class InvestorDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing investors
        $investors = Investor::all();
        
        if ($investors->isEmpty()) {
            $this->command->info('No investors found. Please create investors first.');
            return;
        }

        // Get branches
        $branches = Branch::all();
        
        if ($branches->isEmpty()) {
            $this->command->info('No branches found. Please create branches first.');
            return;
        }

        foreach ($investors as $investor) {
            // Create sample payouts for each investor
            $investments = $investor->investments;
            
            foreach ($investments as $investment) {
                // Create multiple payouts for each investment
                for ($i = 1; $i <= 3; $i++) {
                    $amount = rand(1000, 5000);
                    $taxAmount = $amount * 0.1; // 10% tax
                    $netAmount = $amount - $taxAmount;
                    
                    InvestorPayout::create([
                        'investor_id' => $investor->id,
                        'investment_id' => $investment->id,
                        'branch_id' => $investment->branch_id,
                        'amount' => $amount,
                        'net_amount' => $netAmount,
                        'tax_amount' => $taxAmount,
                        'payout_date' => Carbon::now()->subMonths($i),
                        'payout_type' => 'profit_share',
                        'status' => 'paid',
                        'payment_method' => 'bank_transfer',
                        'reference_number' => 'PAY-' . strtoupper(uniqid()),
                        'notes' => 'Monthly profit share payout',
                        'currency' => 'NPR',
                        'exchange_rate' => 1.0
                    ]);
                }
            }

            // Create sample orders for branches to calculate current value
            foreach ($investments as $investment) {
                $branch = $investment->branch;
                
                // Create orders for the last 30 days
                for ($i = 0; $i < 30; $i++) {
                    Order::create([
                        'branch_id' => $branch->id,
                        'user_id' => 1, // Assuming user ID 1 exists
                        'total_amount' => rand(500, 2000), // Random order amount
                        'status' => 'completed',
                        'created_at' => Carbon::now()->subDays($i),
                        'updated_at' => Carbon::now()->subDays($i)
                    ]);
                }
            }
        }

        $this->command->info('Sample investor data created successfully!');
        $this->command->info('Created payouts and orders for realistic dashboard calculations.');
    }
}
