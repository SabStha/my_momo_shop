<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockItem;
use App\Models\Branch;
use App\Models\BranchInventory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class StockItemSeeder extends Seeder
{
    public function run(): void
    {
        // Get all branches
        $branches = Branch::all();
        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run BranchSeeder first.');
            return;
        }

        $items = [
            [
                'name' => 'Rice',
                'category' => 'Grains',
                'quantity' => 100,
                'unit' => 'kg',
                'cost' => 50.00,
                'expiry' => now()->addMonths(6),
            ],
            [
                'name' => 'Sugar',
                'category' => 'Baking',
                'quantity' => 50,
                'unit' => 'kg',
                'cost' => 30.00,
                'expiry' => now()->addMonths(12),
            ],
            [
                'name' => 'Salt',
                'category' => 'Seasoning',
                'quantity' => 20,
                'unit' => 'kg',
                'cost' => 10.00,
                'expiry' => now()->addMonths(24),
            ],
            [
                'name' => 'Flour',
                'category' => 'Baking',
                'quantity' => 75,
                'unit' => 'kg',
                'cost' => 40.00,
                'expiry' => now()->addMonths(3),
            ],
            [
                'name' => 'Oil',
                'category' => 'Cooking',
                'quantity' => 30,
                'unit' => 'L',
                'cost' => 25.00,
                'expiry' => now()->addMonths(9),
            ],
        ];

        // Create stock items and associate them with branches
        foreach ($items as $item) {
            // Create the stock item
            $stockItem = StockItem::create([
                'name' => $item['name'],
                'category' => $item['category'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'cost' => $item['cost'],
                'expiry' => $item['expiry'],
                'code' => Str::upper(substr($item['name'], 0, 3)) . '-' . Str::random(4)
            ]);

            // Associate with each branch
            foreach ($branches as $branch) {
                // Determine the correct column name based on database schema
                $itemIdColumn = Schema::hasColumn('branch_inventory', 'inventory_item_id') 
                    ? 'inventory_item_id' 
                    : 'stock_item_id';

                BranchInventory::create([
                    'branch_id' => $branch->id,
                    $itemIdColumn => $stockItem->id,
                    'current_stock' => $item['quantity'],
                    'minimum_stock' => $item['quantity'] * 0.2, // 20% of current stock
                    'reorder_point' => $item['quantity'] * 0.3, // 30% of current stock
                    'is_main' => $branch->is_main
                ]);
            }
        }
    }
} 