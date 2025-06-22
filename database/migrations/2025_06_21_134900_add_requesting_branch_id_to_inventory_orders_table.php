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
        Schema::table('inventory_orders', function (Blueprint $table) {
            $table->foreignId('requesting_branch_id')->nullable()->after('branch_id')->constrained('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_orders', function (Blueprint $table) {
            $table->dropForeign(['requesting_branch_id']);
            $table->dropColumn('requesting_branch_id');
        });
    }
};
