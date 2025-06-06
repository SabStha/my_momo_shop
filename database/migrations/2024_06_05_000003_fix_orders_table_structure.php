<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'user_id')) {
                $table->foreignId('user_id')->constrained();
            }
            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status')->default('pending');
            }
            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable();
            }
            if (!Schema::hasColumn('orders', 'billing_address')) {
                $table->text('billing_address')->nullable();
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->nullable();
            }
            if (!Schema::hasColumn('orders', 'payment_status')) {
                $table->string('payment_status')->default('pending');
            }
            if (!Schema::hasColumn('orders', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('orders', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('orders', 'shipping_address')) {
                $table->dropColumn('shipping_address');
            }
            if (Schema::hasColumn('orders', 'billing_address')) {
                $table->dropColumn('billing_address');
            }
            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('orders', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
            if (Schema::hasColumn('orders', 'created_at')) {
                $table->dropColumn('created_at');
            }
            if (Schema::hasColumn('orders', 'updated_at')) {
                $table->dropColumn('updated_at');
            }
        });
    }
}; 