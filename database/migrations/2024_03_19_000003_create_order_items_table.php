<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->unsignedBigInteger('product_id');
                $table->string('item_name');
                $table->integer('quantity');
                $table->decimal('price', 10, 2);
                $table->decimal('subtotal', 10, 2);
                $table->timestamps();

                // Add foreign keys only if the referenced tables exist
                if (Schema::hasTable('orders')) {
                    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
                }
                if (Schema::hasTable('products')) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
} 