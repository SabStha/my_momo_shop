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
        Schema::create('automated_offer_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('trigger_type'); // new_user, abandoned_cart, inactive, birthday, milestone, etc.
            $table->text('description')->nullable();
            $table->json('conditions'); // Trigger conditions (e.g., days_inactive: 14)
            $table->json('offer_template'); // Template for generating the offer
            $table->integer('priority')->default(5); // 1-10, higher = more important
            $table->boolean('is_active')->default(true);
            $table->integer('max_uses_per_user')->nullable(); // null = unlimited
            $table->integer('cooldown_days')->default(30); // Days before same trigger fires again for user
            $table->timestamps();
            
            $table->index('trigger_type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automated_offer_triggers');
    }
};
