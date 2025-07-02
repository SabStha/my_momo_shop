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
        Schema::create('badge_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // AmaKo Gold Plus, Momo Loyalty, Momo Engagement
            $table->string('code')->unique(); // gold_plus, loyalty, engagement
            $table->text('description');
            $table->string('icon'); // Emoji or icon identifier
            $table->boolean('is_public')->default(true); // Gold Plus is invite-only
            $table->boolean('is_active')->default(true);
            $table->json('requirements')->nullable(); // JSON requirements for eligibility
            $table->json('benefits')->nullable(); // JSON benefits description
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_classes');
    }
}; 