<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method')->after('method');
            }
            if (!Schema::hasColumn('payments', 'status')) {
                $table->string('status')->default('pending')->after('payment_method');
            }
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'notes')) {
                $table->text('notes')->nullable()->after('transaction_id');
            }
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'status', 'transaction_id', 'notes']);
        });
    }
}; 