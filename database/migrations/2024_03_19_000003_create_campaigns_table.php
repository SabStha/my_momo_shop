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
        // Create the table first without foreign keys
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('segment_id')->nullable();
            $table->string('offer_type'); // discount, free_shipping, loyalty_points, etc.
            $table->string('offer_value');
            $table->text('copy')->nullable(); // AI-generated campaign copy
            $table->json('targeting_criteria')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled'])->default('draft');
            $table->integer('target_customers')->default(0);
            $table->integer('reached_customers')->default(0);
            $table->integer('converted_customers')->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('roi', 10, 2)->default(0);
            $table->json('metrics')->nullable(); // Store campaign performance metrics
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('name');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
        });

        // Add foreign key constraints after table creation
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('segment_id')->references('id')->on('customer_segments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
}; 