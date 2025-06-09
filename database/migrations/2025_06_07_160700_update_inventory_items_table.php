<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'name')) {
                $table->string('name');
            }
            if (!Schema::hasColumn('inventory_items', 'sku')) {
                $table->string('sku')->unique();
            }
            if (!Schema::hasColumn('inventory_items', 'description')) {
                $table->text('description')->nullable();
            }
            if (!Schema::hasColumn('inventory_items', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->onDelete('set null');
            }
            if (!Schema::hasColumn('inventory_items', 'unit_price')) {
                $table->decimal('unit_price', 10, 2);
            }
            if (!Schema::hasColumn('inventory_items', 'reorder_point')) {
                $table->integer('reorder_point');
            }
            if (!Schema::hasColumn('inventory_items', 'current_stock')) {
                $table->integer('current_stock')->default(0);
            }
            if (!Schema::hasColumn('inventory_items', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            }
            if (!Schema::hasColumn('inventory_items', 'branch_id')) {
                $table->foreignId('branch_id')->constrained('branch_inventories')->onDelete('cascade');
            }
            if (!Schema::hasColumn('inventory_items', 'status')) {
                $table->string('status')->default('active');
            }
        });
    }

    public function down()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'name', 'sku', 'description', 'category_id', 'unit_price',
                'reorder_point', 'current_stock', 'supplier_id', 'branch_id', 'status'
            ]);
        });
    }
}; 