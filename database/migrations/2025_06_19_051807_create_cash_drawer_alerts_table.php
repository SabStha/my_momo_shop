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
        Schema::create('cash_drawer_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->integer('denomination'); // e.g., 1, 2, 5, 10, 20, 50, 100, 500, 1000
            $table->integer('low_threshold')->default(10); // Alert when notes fall below this
            $table->integer('high_threshold')->default(200); // Alert when notes exceed this
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Ensure unique denomination per branch
            $table->unique(['branch_id', 'denomination']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_drawer_alerts');
    }
};
