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
        // This migration is no longer needed as total_amount and profit are already in orders table
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need for down migration
    }
}; 