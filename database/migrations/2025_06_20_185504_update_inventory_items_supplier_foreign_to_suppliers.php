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
        $isSQLite = Schema::getConnection()->getDriverName() === 'sqlite';
        
        if (!$isSQLite) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('set null');
            });
        }
        // For SQLite, we skip foreign key modifications since SQLite doesn't support dropping foreign keys
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSQLite = Schema::getConnection()->getDriverName() === 'sqlite';
        
        if (!$isSQLite) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->dropForeign(['supplier_id']);
                $table->foreign('supplier_id')->references('id')->on('inventory_suppliers')->onDelete('set null');
            });
        }
        // For SQLite, we skip foreign key modifications since SQLite doesn't support dropping foreign keys
    }
};
