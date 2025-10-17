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
        Schema::create('investor_referrals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_investor_id'); // The investor who made the referral
            $table->unsignedBigInteger('referred_investor_id')->nullable(); // The new investor (NULL until they invest)
            $table->string('referral_code')->unique();
            $table->string('referred_email')->nullable();
            $table->string('referred_name')->nullable();
            $table->enum('status', ['pending', 'contacted', 'invested', 'declined'])->default('pending');
            $table->decimal('investment_amount', 12, 2)->nullable(); // Amount invested by referred investor
            $table->decimal('referral_bonus', 12, 2)->nullable(); // Bonus earned by referrer
            $table->decimal('referral_percentage', 5, 2)->default(5.00); // % bonus (default 5%)
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('invested_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('referrer_investor_id')->references('id')->on('investors')->onDelete('cascade');
            $table->foreign('referred_investor_id')->references('id')->on('investors')->onDelete('set null');

            // Indexes
            $table->index('referral_code');
            $table->index(['referrer_investor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_referrals');
    }
};
