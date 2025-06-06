<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'order_id')) {
                $table->foreignId('order_id')->constrained();
            }
            if (!Schema::hasColumn('order_items', 'product_id')) {
                $table->foreignId('product_id')->constrained();
            }
            if (!Schema::hasColumn('order_items', 'quantity')) {
                $table->integer('quantity')->default(1);
            }
            if (!Schema::hasColumn('order_items', 'price')) {
                $table->decimal('price', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('order_items', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('order_items', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('order_items', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            }
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
            if (Schema::hasColumn('order_items', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('order_items', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('order_items', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            if (Schema::hasColumn('order_items', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('order_items', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
}; 