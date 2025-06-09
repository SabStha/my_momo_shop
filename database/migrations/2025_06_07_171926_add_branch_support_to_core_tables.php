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
        // Add branch support to products
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')
                  ->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch support to orders
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')
                  ->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch support to employees
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')
                  ->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch support to tables
        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')
                  ->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch support to wallets
        Schema::table('wallets', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')
                  ->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch support to wallet_transactions
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')
                  ->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch support to payments
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')
                  ->constrained('branch_inventories')
                  ->onDelete('set null');
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove branch support from payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        // Remove branch support from wallet_transactions
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        // Remove branch support from wallets
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        // Remove branch support from tables
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        // Remove branch support from employees
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        // Remove branch support from orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        // Remove branch support from products
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
