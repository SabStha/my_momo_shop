<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\CashDrawerAlert;

class CashDrawerAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all branches
        $branches = Branch::all();

        // Default alert thresholds for each denomination
        $defaultAlerts = [
            1 => ['low' => 50, 'high' => 300],    // Rs 1 - Low: 50 notes, High: 300 notes
            2 => ['low' => 30, 'high' => 200],    // Rs 2 - Low: 30 notes, High: 200 notes
            5 => ['low' => 20, 'high' => 150],    // Rs 5 - Low: 20 notes, High: 150 notes
            10 => ['low' => 15, 'high' => 100],   // Rs 10 - Low: 15 notes, High: 100 notes
            20 => ['low' => 10, 'high' => 80],    // Rs 20 - Low: 10 notes, High: 80 notes
            50 => ['low' => 8, 'high' => 60],     // Rs 50 - Low: 8 notes, High: 60 notes
            100 => ['low' => 5, 'high' => 40],    // Rs 100 - Low: 5 notes, High: 40 notes
            500 => ['low' => 3, 'high' => 20],    // Rs 500 - Low: 3 notes, High: 20 notes
            1000 => ['low' => 0, 'high' => 15],   // Rs 1000 - No low alert (0), High: 15 notes
        ];

        foreach ($branches as $branch) {
            foreach ($defaultAlerts as $denomination => $thresholds) {
                CashDrawerAlert::updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'denomination' => $denomination,
                    ],
                    [
                        'low_threshold' => $thresholds['low'],
                        'high_threshold' => $thresholds['high'],
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
