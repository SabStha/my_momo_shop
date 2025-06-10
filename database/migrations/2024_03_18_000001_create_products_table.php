<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->integer('stock')->default(0);
            $table->string('unit')->default('piece');
            $table->string('category')->nullable();
            $table->string('tag')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('points', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->json('attributes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for frequently queried columns
            $table->index('name');
            $table->index('code');
            $table->index('category');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('branch_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}; 