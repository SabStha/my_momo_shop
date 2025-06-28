<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\InventoryCategory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // Create categories if they don't exist
        $categories = [
            'Grains' => InventoryCategory::firstOrCreate(
                ['name' => 'Grains'],
                ['code' => 'GRA', 'description' => 'Grain products', 'is_active' => true]
            ),
            'Baking' => InventoryCategory::firstOrCreate(
                ['name' => 'Baking'],
                ['code' => 'BAK', 'description' => 'Baking ingredients', 'is_active' => true]
            ),
            'Seasoning' => InventoryCategory::firstOrCreate(
                ['name' => 'Seasoning'],
                ['code' => 'SEA', 'description' => 'Seasonings and spices', 'is_active' => true]
            ),
            'Cooking' => InventoryCategory::firstOrCreate(
                ['name' => 'Cooking'],
                ['code' => 'COO', 'description' => 'Cooking ingredients', 'is_active' => true]
            ),
        ];

        $items = [
            [
                'name' => 'Rice',
                'description' => 'High quality rice for cooking',
                'category' => 'Grains',
                'unit' => 'kg',
                'cost' => 50.00,
                'quantity' => 100,
            ],
            [
                'name' => 'Sugar',
                'description' => 'Granulated white sugar',
                'category' => 'Baking',
                'unit' => 'kg',
                'cost' => 30.00,
                'quantity' => 50,
            ],
            [
                'name' => 'Salt',
                'description' => 'Fine table salt',
                'category' => 'Seasoning',
                'unit' => 'kg',
                'cost' => 10.00,
                'quantity' => 20,
            ],
            [
                'name' => 'Flour',
                'description' => 'All-purpose wheat flour',
                'category' => 'Baking',
                'unit' => 'kg',
                'cost' => 40.00,
                'quantity' => 75,
            ],
            [
                'name' => 'Oil',
                'description' => 'Cooking oil',
                'category' => 'Cooking',
                'unit' => 'L',
                'cost' => 25.00,
                'quantity' => 30,
            ],
        ];

        // Temporarily disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Create inventory items and associate them with branches
        foreach ($items as $item) {
            // Create the inventory item
            $inventoryItem = InventoryItem::create([
                'name' => $item['name'],
                'category_id' => $categories[$item['category']]->id,
                'current_stock' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['cost'],
                'reorder_point' => $item['quantity'] * 0.3, // 30% of current stock
                'code' => Str::upper(substr($item['name'], 0, 3)) . '-' . Str::random(4),
                'status' => 'active',
                'is_locked' => false
            ]);

            // Associate with each branch
            foreach ($branches as $branch) {
                // Determine the correct column name based on database schema
                $itemIdColumn = Schema::hasColumn('branch_inventory', 'inventory_item_id') 
                    ? 'inventory_item_id' 
                    : 'stock_item_id';

                BranchInventory::create([
                    'branch_id' => $branch->id,
                    $itemIdColumn => $inventoryItem->id,
                    'current_stock' => $item['quantity'],
                    'minimum_stock' => $item['quantity'] * 0.2, // 20% of current stock
                    'reorder_point' => $item['quantity'] * 0.3, // 30% of current stock
                    'is_main' => $branch->is_main
                ]);
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
} 