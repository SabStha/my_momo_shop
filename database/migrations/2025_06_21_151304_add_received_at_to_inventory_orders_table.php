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
        if (!Schema::hasColumn('inventory_orders', 'received_at')) {
            Schema::table('inventory_orders', function (Blueprint $table) {
                $table->timestamp('received_at')->nullable()->after('sent_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('inventory_orders', 'received_at')) {
            Schema::table('inventory_orders', function (Blueprint $table) {
                $table->dropColumn('received_at');
            });
        }
    }
};
