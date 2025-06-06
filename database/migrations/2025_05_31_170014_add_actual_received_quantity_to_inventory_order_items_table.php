<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_order_items', function (Blueprint $table) {
            $table->decimal('actual_received_quantity', 10, 2)->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_order_items', function (Blueprint $table) {
            $table->dropColumn('actual_received_quantity');
        });
    }
};
