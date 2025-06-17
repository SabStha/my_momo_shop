<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cash_drawers', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_drawers', 'current_balance')) {
                $table->decimal('current_balance', 10, 2)->default(0)->after('starting_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('cash_drawers', function (Blueprint $table) {
            if (Schema::hasColumn('cash_drawers', 'current_balance')) {
                $table->dropColumn('current_balance');
            }
        });
    }
}; 