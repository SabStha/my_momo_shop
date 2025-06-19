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
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreignId('opened_by')->nullable()->after('status')->constrained('users');
            $table->foreignId('closed_by')->nullable()->after('opened_by')->constrained('users');
            $table->decimal('opening_cash', 10, 2)->default(0)->after('closed_by');
            $table->decimal('closing_cash', 10, 2)->nullable()->after('opening_cash');
            $table->decimal('total_sales', 10, 2)->default(0)->after('closing_cash');
            $table->decimal('total_payments', 10, 2)->default(0)->after('total_sales');
            $table->decimal('total_discounts', 10, 2)->default(0)->after('total_payments');
            $table->decimal('total_taxes', 10, 2)->default(0)->after('total_discounts');
            $table->integer('total_orders')->default(0)->after('total_taxes');
            $table->integer('voided_orders')->default(0)->after('total_orders');
            $table->json('payment_methods_summary')->nullable()->after('voided_orders');
            $table->json('cash_movements')->nullable()->after('payment_methods_summary');
            $table->text('notes')->nullable()->after('cash_movements');
            $table->timestamp('opened_at')->nullable()->after('notes');
            $table->timestamp('closed_at')->nullable()->after('opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['opened_by']);
            $table->dropForeign(['closed_by']);
            $table->dropColumn([
                'opened_by',
                'closed_by',
                'opening_cash',
                'closing_cash',
                'total_sales',
                'total_payments',
                'total_discounts',
                'total_taxes',
                'total_orders',
                'voided_orders',
                'payment_methods_summary',
                'cash_movements',
                'notes',
                'opened_at',
                'closed_at'
            ]);
        });
    }
};
