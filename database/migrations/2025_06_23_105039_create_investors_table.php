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
        Schema::create('investors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('company_name')->nullable();
            $table->enum('investment_type', ['individual', 'corporate', 'angel', 'venture_capital'])->default('individual');
            $table->decimal('total_investment_amount', 15, 2)->default(0);
            $table->timestamp('investment_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active');
            $table->text('notes')->nullable();
            $table->string('tax_id')->nullable();
            $table->json('bank_details')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('website')->nullable();
            $table->json('social_media')->nullable();
            $table->enum('risk_profile', ['conservative', 'moderate', 'aggressive'])->default('moderate');
            $table->enum('investment_horizon', ['short_term', 'medium_term', 'long_term'])->default('medium_term');
            $table->string('preferred_communication')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verification_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investors');
    }
};
