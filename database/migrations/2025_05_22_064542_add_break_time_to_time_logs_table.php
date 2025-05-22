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
        Schema::table('time_logs', function (Blueprint $table) {
            $table->timestamp('break_start')->nullable();
            $table->timestamp('break_end')->nullable();
            $table->string('status')->default('active'); // active, on_break, completed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_logs', function (Blueprint $table) {
            $table->dropColumn(['break_start', 'break_end', 'status']);
        });
    }
};
