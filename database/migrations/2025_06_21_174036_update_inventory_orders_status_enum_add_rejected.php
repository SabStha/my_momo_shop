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
        // Update the ENUM to include rejected
        DB::statement("ALTER TABLE inventory_orders MODIFY COLUMN status ENUM('pending', 'sent', 'supplier_confirmed', 'processed', 'received', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove rejected from ENUM
        DB::statement("ALTER TABLE inventory_orders MODIFY COLUMN status ENUM('pending', 'sent', 'supplier_confirmed', 'processed', 'received', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};
