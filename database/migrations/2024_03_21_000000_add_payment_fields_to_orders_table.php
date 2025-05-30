<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        if (!Schema::hasColumn('orders', 'payment_status')) {
            $table->string('payment_status')->default('unpaid');
        }

        if (!Schema::hasColumn('orders', 'payment_method')) {
            $table->string('payment_method')->nullable();
        }

        if (!Schema::hasColumn('orders', 'amount_received')) {
            $table->decimal('amount_received', 10, 2)->nullable();
        }

        if (!Schema::hasColumn('orders', 'change')) {
            $table->decimal('change', 10, 2)->nullable();
        }

        if (!Schema::hasColumn('orders', 'guest_name')) {
            $table->string('guest_name')->nullable();
        }

        if (!Schema::hasColumn('orders', 'guest_email')) {
            $table->string('guest_email')->nullable();
        }
    });
}


    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'amount_received',
                'change',
                'guest_name',
                'guest_email'
            ]);
        });
    }
}; 