<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        } else {
            // For SQLite, just drop the column without dropping foreign key
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropColumn('employee_id');
            });
        }
    }
}; 