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
        Schema::create('cash_denominations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "1000", "500", "100", "50", "20", "10", "5", "1", "0.5", "0.25"
            $table->decimal('value', 10, 2); // The actual value of the denomination
            $table->integer('quantity')->default(0); // Number of pieces available
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create a table to track changes in cash denominations
        Schema::create('cash_denomination_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_denomination_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->string('change_type'); // 'add', 'remove', 'adjust'
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_denomination_changes');
        Schema::dropIfExists('cash_denominations');
    }
}; 