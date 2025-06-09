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
        // Update branches table
        Schema::table('branches', function (Blueprint $table) {
            // Make fields nullable
            $table->text('address')->nullable()->change();
            $table->string('contact_person')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
        });

        // Migrate data from branch_inventories to branches
        if (Schema::hasTable('branch_inventories')) {
            DB::statement('
                INSERT INTO branches (name, code, address, contact_person, is_active, is_main, created_at, updated_at)
                SELECT name, code, address, contact, is_active, is_main, created_at, updated_at
                FROM branch_inventories
            ');
        }

        // Create a main branch if none exists
        if (!DB::table('branches')->where('is_main', true)->exists()) {
            DB::table('branches')->insert([
                'name' => 'Main Branch',
                'code' => 'MAIN',
                'is_active' => true,
                'is_main' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Revert nullable fields
            $table->text('address')->nullable(false)->change();
            $table->string('contact_person')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
        });
    }
};
