<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Create inventory_categories table if it doesn't exist
        if (!Schema::hasTable('inventory_categories')) {
            Schema::create('inventory_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Create branch_inventories table if it doesn't exist
        if (!Schema::hasTable('branch_inventories')) {
            Schema::create('branch_inventories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('address')->nullable();
                $table->string('contact')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create suppliers table if it doesn't exist
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('contact')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->foreignId('branch_id')->nullable()->constrained('branch_inventories')->onDelete('cascade');
                $table->boolean('is_shared')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create inventory_items table if it doesn't exist
        if (!Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('sku')->unique();
                $table->text('description')->nullable();
                $table->foreignId('category_id')->constrained('inventory_categories')->onDelete('restrict');
                $table->string('unit');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('reorder_point', 10, 2)->default(0);
                $table->decimal('current_stock', 10, 2)->default(0);
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
                $table->foreignId('branch_id')->constrained('branch_inventories')->onDelete('cascade');
                $table->string('status')->default('active');
                $table->boolean('is_locked')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create inventory_transactions table if it doesn't exist
        if (!Schema::hasTable('inventory_transactions')) {
            Schema::create('inventory_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['purchase', 'sale', 'adjustment', 'return', 'waste']);
                $table->decimal('quantity', 10, 2);
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_amount', 10, 2);
                $table->text('notes')->nullable();
                $table->string('reference_number')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->timestamps();
            });
        }

        // Create daily_stock_checks table if it doesn't exist
        if (!Schema::hasTable('daily_stock_checks')) {
            Schema::create('daily_stock_checks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->decimal('quantity_checked', 10, 2);
                $table->text('notes')->nullable();
                $table->timestamp('checked_at');
                $table->timestamps();
            });
        }

        // Create inventory_orders table if it doesn't exist
        if (!Schema::hasTable('inventory_orders')) {
            Schema::create('inventory_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
                $table->foreignId('branch_id')->constrained('branch_inventories')->onDelete('cascade');
                $table->string('order_number')->unique();
                $table->decimal('total_amount', 10, 2);
                $table->enum('status', ['pending', 'confirmed', 'received', 'cancelled'])->default('pending');
                $table->timestamp('ordered_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Create inventory_order_items table if it doesn't exist
        if (!Schema::hasTable('inventory_order_items')) {
            Schema::create('inventory_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_order_id')->constrained()->onDelete('cascade');
                $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_price', 10, 2);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();

        // Drop tables in reverse order of their dependencies
        Schema::dropIfExists('inventory_order_items');
        Schema::dropIfExists('inventory_orders');
        Schema::dropIfExists('daily_stock_checks');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('branch_inventories');
        Schema::dropIfExists('inventory_categories');

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }
}; 