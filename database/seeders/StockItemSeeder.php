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
                'unit' => 'kg',
                'unit_price' => 50.00,
                'current_stock' => 100,
                'reorder_point' => 30,
            ],
            [
                'name' => 'Sugar',
                'description' => 'Granulated white sugar',
                'unit' => 'kg',
                'unit_price' => 30.00,
                'current_stock' => 50,
                'reorder_point' => 15,
            ],
            [
                'name' => 'Salt',
                'description' => 'Fine table salt',
                'unit' => 'kg',
                'unit_price' => 10.00,
                'current_stock' => 20,
                'reorder_point' => 6,
            ],
            [
                'name' => 'Flour',
                'description' => 'All-purpose wheat flour',
                'unit' => 'kg',
                'unit_price' => 40.00,
                'current_stock' => 75,
                'reorder_point' => 25,
            ],
            [
                'name' => 'Oil',
                'description' => 'Cooking oil',
                'unit' => 'L',
                'unit_price' => 25.00,
                'current_stock' => 30,
                'reorder_point' => 10,
            ],
        ];

<<<<<<< HEAD
        // Temporarily disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

=======
>>>>>>> main
        // Create inventory items and associate them with branches
        foreach ($items as $item) {
            // Create the inventory item
            $inventoryItem = InventoryItem::create([
                'name' => $item['name'],
<<<<<<< HEAD
                'code' => Str::upper(substr($item['name'], 0, 3)) . '-' . Str::random(4),
                'description' => $item['description'],
                'unit' => $item['unit'],
                'unit_price' => $item['unit_price'],
                'current_stock' => $item['current_stock'],
                'reorder_point' => $item['reorder_point'],
                'status' => 'active',
                'is_locked' => false,
=======
                'category_id' => $categories[$item['category']]->id,
                'current_stock' => $item['quantity'],
                'unit' => $item['unit'],
                'unit_price' => $item['cost'],
                'reorder_point' => $item['quantity'] * 0.3, // 30% of current stock
                'code' => Str::upper(substr($item['name'], 0, 3)) . '-' . Str::random(4),
                'status' => 'active',
                'is_locked' => false
>>>>>>> main
            ]);

            // Associate with each branch
            foreach ($branches as $branch) {
                // Determine the correct column name based on database schema
                $itemIdColumn = Schema::hasColumn('branch_inventory', 'inventory_item_id') 
                    ? 'inventory_item_id' 
                    : 'stock_item_id';

                BranchInventory::create([
                    'branch_id' => $branch->id,
<<<<<<< HEAD
                    $itemIdColumn => $inventoryItem->id,
                    'current_stock' => $item['current_stock'],
                    'minimum_stock' => $item['current_stock'] * 0.2, // 20% of current stock
                    'reorder_point' => $item['reorder_point'],
=======
                    'inventory_item_id' => $inventoryItem->id,
                    'current_stock' => $item['quantity'],
                    'minimum_stock' => $item['quantity'] * 0.2, // 20% of current stock
                    'reorder_point' => $item['quantity'] * 0.3, // 30% of current stock
>>>>>>> main
                    'is_main' => $branch->is_main
                ]);
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
} 