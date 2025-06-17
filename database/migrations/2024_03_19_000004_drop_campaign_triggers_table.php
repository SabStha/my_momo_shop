<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::dropIfExists('campaign_triggers');
    }

    public function down()
    {
        // No need to recreate the table in down() as it will be handled by the original migration
    }
}; 