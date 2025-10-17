<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Investor;
use App\Models\InvestorInvestment;
use App\Models\InvestorPayout;
use Illuminate\Support\Facades\DB;

class CleanupDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeder to clean up demo investor data.
     */
    public function run(): void
    {
        $this->command->info('Cleaning up demo investor data...');

        // Delete all investor-related data
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        InvestorPayout::truncate();
        $this->command->info('✓ Cleared investor payouts');
        
        InvestorInvestment::truncate();
        $this->command->info('✓ Cleared investor investments');
        
        Investor::truncate();
        $this->command->info('✓ Cleared investors');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Demo data cleanup completed!');
        $this->command->warn('Note: The investor dashboard will now show only real data when you create actual investor records.');
    }
}

