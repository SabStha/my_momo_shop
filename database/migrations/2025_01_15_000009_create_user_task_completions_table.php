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
        Schema::create('user_task_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('credit_task_id')->constrained()->onDelete('cascade');
            $table->integer('credits_earned');
            $table->json('completion_data')->nullable(); // Data about how task was completed
            $table->timestamp('completed_at');
            $table->timestamps();
            
            $table->index(['user_id', 'credit_task_id']);
            $table->index('completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_task_completions');
    }
}; 