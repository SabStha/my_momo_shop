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
        Schema::create('user_offer_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->time('preferred_notification_time')->default('11:00:00');
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->enum('frequency_preference', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->json('categories_of_interest')->nullable();
            $table->boolean('allow_personalized_offers')->default(true);
            $table->boolean('allow_flash_sales')->default(true);
            $table->boolean('allow_loyalty_rewards')->default(true);
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_offer_preferences');
    }
};
