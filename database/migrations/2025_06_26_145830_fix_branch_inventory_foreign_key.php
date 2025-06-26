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
        Schema::table('branch_inventory', function (Blueprint $table) {
            // Drop any foreign key constraint on inventory_item_id
            try {
                DB::statement('ALTER TABLE branch_inventory DROP FOREIGN KEY branch_inventory_stock_item_id_foreign');
            } catch (\Exception $e) {}
            try {
                DB::statement('ALTER TABLE branch_inventory DROP FOREIGN KEY branch_inventory_inventory_item_id_foreign');
            } catch (\Exception $e) {}
            // Add the correct foreign key
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_inventory', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            // Optionally, you could add back the old foreign key here if needed
        });
    }
};
