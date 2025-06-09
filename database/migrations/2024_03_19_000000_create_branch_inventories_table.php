<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('branch_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('contact')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add branch_id to all inventory tables
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')->onDelete('cascade');
        });

        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')->onDelete('cascade');
        });

        Schema::table('inventory_suppliers', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')->onDelete('cascade');
        });

        Schema::table('inventory_orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')->onDelete('cascade');
        });

        Schema::table('inventory_order_items', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('inventory_order_items', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('inventory_orders', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('inventory_suppliers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        
        Schema::dropIfExists('branch_inventories');
    }
}; 