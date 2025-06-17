<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->boolean('requires_online_payment')->default(true)->after('is_active');
            $table->string('icon')->nullable()->after('requires_online_payment');
            $table->integer('sort_order')->default(0)->after('icon');
        });
    }

    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['requires_online_payment', 'icon', 'sort_order']);
        });
    }
}; 