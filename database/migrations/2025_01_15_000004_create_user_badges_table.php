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
        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_tier_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_rank_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_class_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamp('earned_at');
            $table->timestamp('expires_at')->nullable();
            $table->json('earned_data')->nullable(); // Store data about how it was earned
            $table->timestamps();
            
            $table->unique(['user_id', 'badge_tier_id']);
            $table->index(['user_id', 'badge_class_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_badges');
    }
}; 