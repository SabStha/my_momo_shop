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
        Schema::create('badge_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_class_id')->constrained()->onDelete('cascade');
            $table->integer('current_points')->default(0);
            $table->integer('total_points_earned')->default(0);
            $table->json('progress_data')->nullable(); // Store detailed progress data
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'badge_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_progress');
    }
}; 