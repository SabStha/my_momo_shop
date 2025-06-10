<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('stock_item_id')->constrained('stock_items')->onDelete('cascade');
            $table->decimal('current_stock', 8, 2)->default(0);
            $table->decimal('minimum_stock', 8, 2)->default(0);
            $table->decimal('reorder_point', 8, 2)->default(0);
            $table->boolean('is_main')->default(false);
            $table->timestamps();

            $table->unique(['branch_id', 'stock_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_inventory');
    }
}; 