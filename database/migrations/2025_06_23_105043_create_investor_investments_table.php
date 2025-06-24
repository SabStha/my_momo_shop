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
        Schema::create('investor_investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('investment_amount', 15, 2);
            $table->decimal('ownership_percentage', 5, 2); // Up to 100.00%
            $table->timestamp('investment_date');
            $table->enum('status', ['active', 'inactive', 'pending', 'sold'])->default('active');
            $table->json('terms_conditions')->nullable();
            $table->text('exit_strategy')->nullable();
            $table->decimal('expected_return', 5, 2)->nullable(); // Percentage
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('medium');
            $table->enum('investment_type', ['equity', 'debt', 'convertible_note'])->default('equity');
            $table->timestamp('maturity_date')->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable(); // Percentage
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'annually'])->default('monthly');
            $table->text('notes')->nullable();
            $table->json('documents')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approval_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_investments');
    }
};
