<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_id')->nullable()->after('id');
            $table->foreign('creator_id')->references('id')->on('creators')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['creator_id']);
            $table->dropColumn('creator_id');
        });
    }
}; 