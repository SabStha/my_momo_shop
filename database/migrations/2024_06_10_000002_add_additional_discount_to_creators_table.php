<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->integer('additional_discount')->default(0);
        });
    }

    public function down()
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->dropColumn('additional_discount');
        });
    }
}; 