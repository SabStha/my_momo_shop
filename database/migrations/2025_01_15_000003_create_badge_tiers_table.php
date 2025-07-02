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
        Schema::create('badge_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('badge_rank_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Tier 1, Tier 2, Tier 3
            $table->integer('level'); // 1, 2, 3
            $table->text('description');
            $table->json('requirements')->nullable(); // JSON requirements for this tier
            $table->json('benefits')->nullable(); // JSON benefits for this tier
            $table->integer('points_required')->default(0); // Points needed to unlock
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['badge_rank_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_tiers');
    }
}; 