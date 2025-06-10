<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Table;
use App\Models\Branch;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    public function run()
    {
        // Get all branches
        $branches = Branch::all();
        
        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run BranchSeeder first.');
            return;
        }

        // Create tables for each branch
        foreach ($branches as $branch) {
            // Create 10 tables for each branch
            for ($i = 1; $i <= 10; $i++) {
                $tableNumber = $branch->code . '-T' . str_pad($i, 3, '0', STR_PAD_LEFT);
                
                Table::firstOrCreate(
                    ['number' => $tableNumber],
                    [
                        'name' => 'Table ' . $i,
                        'branch_id' => $branch->id,
                        'capacity' => rand(2, 8), // Random capacity between 2 and 8
                        'is_occupied' => false,
                        'is_active' => true
                    ]
                );
            }
        }
    }
} 