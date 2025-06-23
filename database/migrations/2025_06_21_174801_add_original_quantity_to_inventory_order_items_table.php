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
        Schema::table('inventory_order_items', function (Blueprint $table) {
            $table->decimal('original_quantity', 10, 2)->nullable()->after('quantity');
        });

        // Populate original_quantity with current quantity for existing records
        DB::statement('UPDATE inventory_order_items SET original_quantity = quantity WHERE original_quantity IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_order_items', function (Blueprint $table) {
            $table->dropColumn('original_quantity');
        });
    }
};
