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
        // Create suppliers table if not exists
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->timestamps();
            });
        }

        // Create inventory_items table if not exists
        if (!Schema::hasTable('inventory_items')) {
            Schema::create('inventory_items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('sku')->unique();
                $table->text('description')->nullable();
                $table->string('unit');
                $table->decimal('quantity', 10, 2)->default(0);
                $table->decimal('minimum_quantity', 10, 2)->default(0);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->timestamps();
            });
        }

        // Create inventory_orders table if not exists
        if (!Schema::hasTable('inventory_orders')) {
            Schema::create('inventory_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
                $table->enum('status', ['pending', 'sent', 'received', 'partially_received', 'cancelled'])->default('pending');
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('received_at')->nullable();
                $table->timestamps();
            });
        }

        // Create inventory_order_items table if not exists
        if (!Schema::hasTable('inventory_order_items')) {
            Schema::create('inventory_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('inventory_order_id')->constrained()->onDelete('cascade');
                $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
                $table->decimal('quantity', 10, 2);
                $table->decimal('actual_received_quantity', 10, 2)->nullable();
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_price', 10, 2);
                $table->timestamps();
            });
        }

        // Create activity_log table if not exists
        if (!Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('log_name')->nullable();
                $table->text('description');
                $table->nullableMorphs('subject', 'subject');
                $table->nullableMorphs('causer', 'causer');
                $table->json('properties')->nullable();
                $table->uuid('batch_uuid')->nullable();
                $table->timestamps();
                $table->index('log_name');
            });
        }

        // Add missing columns to existing tables
        if (Schema::hasTable('inventory_items') && !Schema::hasColumn('inventory_items', 'quantity')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->decimal('quantity', 10, 2)->default(0)->after('unit');
            });
        }

        if (Schema::hasTable('inventory_order_items') && !Schema::hasColumn('inventory_order_items', 'actual_received_quantity')) {
            Schema::table('inventory_order_items', function (Blueprint $table) {
                $table->decimal('actual_received_quantity', 10, 2)->nullable()->after('quantity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to drop tables in down() since they might be used by other parts of the application
        // Instead, we'll just remove the columns we added
        if (Schema::hasTable('inventory_items')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_items', 'quantity')) {
                    $table->dropColumn('quantity');
                }
            });
        }

        if (Schema::hasTable('inventory_order_items')) {
            Schema::table('inventory_order_items', function (Blueprint $table) {
                if (Schema::hasColumn('inventory_order_items', 'actual_received_quantity')) {
                    $table->dropColumn('actual_received_quantity');
                }
            });
        }
    }
};
