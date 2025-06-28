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
        Schema::table('users', function (Blueprint $table) {
            // Drop the existing address column
            $table->dropColumn('address');
            
            // Add new detailed address fields
            $table->string('city')->nullable()->after('phone');
            $table->string('ward_number')->nullable()->after('city');
            $table->string('area_locality')->nullable()->after('ward_number');
            $table->string('building_name')->nullable()->after('area_locality');
            $table->text('detailed_directions')->nullable()->after('building_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the new detailed address fields
            $table->dropColumn(['city', 'ward_number', 'area_locality', 'building_name', 'detailed_directions']);
            
            // Restore the original address field
            $table->text('address')->nullable()->after('phone');
        });
    }
};
