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
        Schema::create('credit_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description');
            $table->enum('type', ['one_time', 'daily', 'weekly', 'monthly']);
            $table->integer('credits_reward');
            $table->json('requirements')->nullable(); // What needs to be done
            $table->json('validation_rules')->nullable(); // How to validate completion
            $table->boolean('requires_badge')->default(false);
            $table->foreignId('required_badge_class_id')->nullable()->constrained('badge_classes');
            $table->boolean('is_active')->default(true);
            $table->integer('max_completions')->nullable(); // null = unlimited
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_tasks');
    }
}; 