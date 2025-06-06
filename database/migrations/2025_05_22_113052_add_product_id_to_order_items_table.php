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
        if (Schema::hasTable('order_items') && Schema::hasTable('products')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('order_items', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->after('order_id');
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                if (Schema::hasColumn('order_items', 'product_id')) {
                    $table->dropForeign(['product_id']);
                    $table->dropColumn('product_id');
                }
            });
        }
    }

};
