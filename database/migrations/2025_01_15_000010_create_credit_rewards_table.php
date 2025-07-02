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
        Schema::create('credit_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('credits_cost');
            $table->enum('type', ['free_item', 'discount', 'privilege', 'physical']);
            $table->json('reward_data')->nullable(); // Specific details about the reward
            $table->boolean('requires_badge')->default(false);
            $table->foreignId('required_badge_class_id')->nullable()->constrained('badge_classes');
            $table->boolean('is_active')->default(true);
            $table->integer('stock_quantity')->nullable(); // null = unlimited
            $table->integer('redeemed_count')->default(0);
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_rewards');
    }
}; 