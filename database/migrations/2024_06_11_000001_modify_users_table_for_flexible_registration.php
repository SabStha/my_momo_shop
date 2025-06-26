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
            // First, try to drop any existing constraints
            try {
                DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_or_phone');
            } catch (\Exception $e) {
                // Ignore if constraint doesn't exist
            }
        }

        // Make columns nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('name')->nullable()->change();
        });

        if (!$isSQLite) {
            // Add the new constraint with a unique name
            try {
                DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_or_phone_new CHECK (email IS NOT NULL OR phone IS NOT NULL)');
            } catch (\Exception $e) {
                // If constraint already exists, try to drop it first
                DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_or_phone_new');
                DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_or_phone_new CHECK (email IS NOT NULL OR phone IS NOT NULL)');
            }
        }
        // For SQLite, constraints are handled differently and we skip them
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSQLite = Schema::getConnection()->getDriverName() === 'sqlite';
        
        if (!$isSQLite) {
            // Drop the new constraint
            try {
                DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_or_phone_new');
            } catch (\Exception $e) {
                // Ignore if constraint doesn't exist
            }
        }

        // Make columns required again
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
        });

        if (!$isSQLite) {
            // Add back the original constraint
            try {
                DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_or_phone CHECK (email IS NOT NULL OR phone IS NOT NULL)');
            } catch (\Exception $e) {
                // If constraint already exists, try to drop it first
                DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_email_or_phone');
                DB::statement('ALTER TABLE users ADD CONSTRAINT users_email_or_phone CHECK (email IS NOT NULL OR phone IS NOT NULL)');
            }
        }
        // For SQLite, constraints are handled differently and we skip them
    }
}; 