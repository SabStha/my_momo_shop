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
        Schema::table('users', function (Blueprint $table) {
            // Make email nullable
            $table->string('email')->nullable()->change();
            
            // Make phone unique and not nullable
            $table->string('phone')->nullable(false)->unique()->change();
            
            // Add a check constraint to ensure at least one of email or phone is provided
            DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_or_phone CHECK (email IS NOT NULL OR phone IS NOT NULL)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove the check constraint
            DB::statement('ALTER TABLE users DROP CONSTRAINT users_email_or_phone');
            
            // Revert email to not nullable
            $table->string('email')->nullable(false)->change();
            
            // Revert phone to nullable
            $table->string('phone')->nullable()->change();
        });
    }
}; 