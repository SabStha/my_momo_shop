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
        Schema::create('ama_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('ama_credit_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['earned', 'spent', 'expired']);
            $table->integer('amount');
            $table->string('description');
            $table->string('source')->nullable(); // task, badge, manual, etc.
            $table->json('metadata')->nullable(); // Additional data about the transaction
            $table->timestamp('expires_at')->nullable(); // When credits expire (1 year from earning)
            $table->boolean('is_expired')->default(false);
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index(['expires_at', 'is_expired']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ama_credit_transactions');
    }
}; 