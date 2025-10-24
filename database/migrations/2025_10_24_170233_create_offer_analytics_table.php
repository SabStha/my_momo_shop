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
        Schema::create('offer_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('action', ['received', 'viewed', 'claimed', 'applied', 'used', 'expired', 'ignored']);
            $table->timestamp('timestamp')->useCurrent();
            $table->json('device_info')->nullable(); // Device type, OS, app version
            $table->json('session_data')->nullable(); // Session ID, page context, etc.
            $table->string('notification_id')->nullable(); // If triggered from notification
            $table->decimal('discount_value', 10, 2)->nullable(); // Actual discount applied
            $table->timestamps();
            
            $table->index(['offer_id', 'user_id']);
            $table->index('action');
            $table->index('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_analytics');
    }
};
