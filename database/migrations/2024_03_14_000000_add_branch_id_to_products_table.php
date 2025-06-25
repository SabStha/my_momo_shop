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
        // This migration is no longer needed as branch_id is already in products table
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need for down migration
    }
}; 