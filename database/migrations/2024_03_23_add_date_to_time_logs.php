<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('time_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('time_logs', 'date')) {
                $table->date('date')->after('branch_id');
                $table->index('date');
            }
        });
    }

    public function down()
    {
        Schema::table('time_logs', function (Blueprint $table) {
            if (Schema::hasColumn('time_logs', 'date')) {
                $table->dropIndex(['date']);
                $table->dropColumn('date');
            }
        });
    }
}; 