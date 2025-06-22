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
        // Drop the existing unique constraint with old name
        try {
            DB::statement('ALTER TABLE branch_inventory DROP INDEX branch_inventory_branch_id_stock_item_id_unique');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Drop the existing index with old name
        try {
            DB::statement('ALTER TABLE branch_inventory DROP INDEX branch_inventory_stock_item_id_foreign');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Add the new foreign key constraint for inventory_item_id
        Schema::table('branch_inventory', function (Blueprint $table) {
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
        });

        // Add the new unique constraint
        Schema::table('branch_inventory', function (Blueprint $table) {
            $table->unique(['branch_id', 'inventory_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new foreign key constraint
        try {
            DB::statement('ALTER TABLE branch_inventory DROP FOREIGN KEY branch_inventory_inventory_item_id_foreign');
        } catch (\Exception $e) {
            // Foreign key may not exist, continue
        }

        // Drop the new unique constraint
        try {
            DB::statement('ALTER TABLE branch_inventory DROP INDEX branch_inventory_branch_id_inventory_item_id_unique');
        } catch (\Exception $e) {
            // Index doesn't exist, continue
        }

        // Recreate the old constraints with correct column name
        Schema::table('branch_inventory', function (Blueprint $table) {
            $table->unique(['branch_id', 'inventory_item_id'], 'branch_inventory_branch_id_stock_item_id_unique');
            $table->index('inventory_item_id', 'branch_inventory_stock_item_id_foreign');
        });
    }
};
