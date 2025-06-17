<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('conditions');
            $table->json('actions');
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['branch_id', 'is_active']);
            $table->index('priority');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rules');
    }
}; 