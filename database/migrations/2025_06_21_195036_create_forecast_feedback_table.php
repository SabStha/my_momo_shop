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
        Schema::create('forecast_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('inventory_item_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('forecast_type'); // 'main_branch' or 'branch'
            $table->decimal('forecasted_quantity', 10, 2);
            $table->decimal('actual_quantity_used', 10, 2)->nullable();
            $table->decimal('accuracy_percentage', 5, 2)->nullable();
            $table->text('forecast_reasoning')->nullable();
            $table->json('forecast_context')->nullable(); // Store the context used for forecasting
            $table->json('actual_usage_context')->nullable(); // Store actual usage context
            $table->string('feedback_type')->default('automatic'); // 'automatic' or 'manual'
            $table->text('manual_feedback')->nullable(); // For manual corrections
            $table->boolean('was_accurate')->nullable(); // True if within acceptable range
            $table->timestamp('forecast_date');
            $table->timestamp('usage_date')->nullable();
            $table->timestamps();

            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            
            $table->index(['forecast_type', 'forecast_date']);
            $table->index(['inventory_item_id', 'forecast_date']);
            $table->index(['branch_id', 'forecast_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_feedback');
    }
};
