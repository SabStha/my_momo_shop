<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check which column exists in the branch_inventory table
        $hasInventoryItemId = Schema::hasColumn('branch_inventory', 'inventory_item_id');
        $hasStockItemId = Schema::hasColumn('branch_inventory', 'stock_item_id');
        
        if ($hasInventoryItemId) {
            // Clean up orphaned branch_inventory records that reference non-existent inventory_items
            $deletedCount = DB::table('branch_inventory')
                ->leftJoin('inventory_items', 'branch_inventory.inventory_item_id', '=', 'inventory_items.id')
                ->whereNull('inventory_items.id')
                ->delete();
        } elseif ($hasStockItemId) {
            // Clean up orphaned branch_inventory records that reference non-existent stock_items
            $deletedCount = DB::table('branch_inventory')
                ->leftJoin('stock_items', 'branch_inventory.stock_item_id', '=', 'stock_items.id')
                ->whereNull('stock_items.id')
                ->delete();
        } else {
            // No relevant columns found, skip cleanup
            $deletedCount = 0;
        }

        // Log the cleanup for audit purposes
        \Log::info("Cleanup migration: Deleted {$deletedCount} orphaned branch_inventory records");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration only cleans up data, so there's nothing to reverse
        // The orphaned records were invalid anyway
    }
};
