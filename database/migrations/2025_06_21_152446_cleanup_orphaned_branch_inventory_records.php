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
        // Clean up orphaned branch_inventory records that reference non-existent inventory_items
        // This is safe to run after changing the foreign key reference
        $deletedCount = DB::table('branch_inventory')
            ->leftJoin('inventory_items', 'branch_inventory.inventory_item_id', '=', 'inventory_items.id')
            ->whereNull('inventory_items.id')
            ->delete();

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
