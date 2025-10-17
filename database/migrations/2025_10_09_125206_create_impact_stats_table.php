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
        Schema::create('impact_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable(); // NULL for company-wide stats
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('donation_amount', 12, 2)->default(0);
            $table->decimal('donation_percentage', 5, 2)->default(0); // % of sales donated
            $table->integer('plates_funded')->default(0); // Number of plates funded
            $table->integer('dogs_saved')->default(0); // Estimated dogs saved
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');

            // Indexes
            $table->index(['branch_id', 'period_type', 'period_start']);
            $table->index(['period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impact_stats');
    }
};
