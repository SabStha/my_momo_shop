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
        $isSQLite = Schema::getConnection()->getDriverName() === 'sqlite';
        
        if (!$isSQLite) {
            // Update the ENUM to include rejected (MySQL/PostgreSQL only)
            DB::statement("ALTER TABLE inventory_orders MODIFY COLUMN status ENUM('pending', 'sent', 'supplier_confirmed', 'processed', 'received', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending'");
        }
        // For SQLite, we skip ENUM modifications since SQLite doesn't support ENUM types
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSQLite = Schema::getConnection()->getDriverName() === 'sqlite';
        
        if (!$isSQLite) {
            // Remove rejected from ENUM (MySQL/PostgreSQL only)
            DB::statement("ALTER TABLE inventory_orders MODIFY COLUMN status ENUM('pending', 'sent', 'supplier_confirmed', 'processed', 'received', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
        // For SQLite, we skip ENUM modifications since SQLite doesn't support ENUM types
    }
};
