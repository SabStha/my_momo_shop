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
        Schema::table('cash_drawer_sessions', function (Blueprint $table) {
            $table->decimal('discrepancy', 12, 2)->nullable()->after('notes');
            $table->integer('session_duration')->nullable()->after('discrepancy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_drawer_sessions', function (Blueprint $table) {
            $table->dropColumn('discrepancy');
            $table->dropColumn('session_duration');
        });
    }
};
