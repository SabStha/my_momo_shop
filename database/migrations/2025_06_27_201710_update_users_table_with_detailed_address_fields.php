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
            // Drop the existing address column if it exists
            if (Schema::hasColumn('users', 'address')) {
                $table->dropColumn('address');
            }
            
            // Add new detailed address fields if they don't exist
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'ward_number')) {
                $table->string('ward_number')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'area_locality')) {
                $table->string('area_locality')->nullable()->after('ward_number');
            }
            if (!Schema::hasColumn('users', 'building_name')) {
                $table->string('building_name')->nullable()->after('area_locality');
            }
            if (!Schema::hasColumn('users', 'detailed_directions')) {
                $table->text('detailed_directions')->nullable()->after('building_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the new detailed address fields if they exist
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'city')) {
                $columnsToDrop[] = 'city';
            }
            if (Schema::hasColumn('users', 'ward_number')) {
                $columnsToDrop[] = 'ward_number';
            }
            if (Schema::hasColumn('users', 'area_locality')) {
                $columnsToDrop[] = 'area_locality';
            }
            if (Schema::hasColumn('users', 'building_name')) {
                $columnsToDrop[] = 'building_name';
            }
            if (Schema::hasColumn('users', 'detailed_directions')) {
                $columnsToDrop[] = 'detailed_directions';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
            
            // Restore the original address field if it doesn't exist
            if (!Schema::hasColumn('users', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
        });
    }
};
