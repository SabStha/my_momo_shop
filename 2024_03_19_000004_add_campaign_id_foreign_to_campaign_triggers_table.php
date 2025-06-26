<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('campaign_triggers', function (Blueprint $table) {
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
        });
    }
    public function down() {
        Schema::table('campaign_triggers', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
        });
    }
}; 