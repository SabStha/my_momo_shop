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
        // First, update any existing 'ordered' status to 'sent'
        DB::table('inventory_orders')
            ->where('status', 'ordered')
            ->update(['status' => 'sent']);

        // Then modify the enum to include 'sent' and remove 'ordered'
        DB::statement("ALTER TABLE inventory_orders MODIFY COLUMN status ENUM('pending', 'sent', 'received', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, update any existing 'sent' status to 'ordered'
        DB::table('inventory_orders')
            ->where('status', 'sent')
            ->update(['status' => 'ordered']);

        // Then modify the enum back to include 'ordered' and remove 'sent'
        DB::statement("ALTER TABLE inventory_orders MODIFY COLUMN status ENUM('pending', 'ordered', 'received', 'cancelled') DEFAULT 'pending'");
    }
};
