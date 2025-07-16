<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make user_id nullable (no foreign key constraint exists)
        DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NULL;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert user_id to NOT NULL
        DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NOT NULL;');
    }
};
