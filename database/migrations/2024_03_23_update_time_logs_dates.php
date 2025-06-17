<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update existing records to use clock_in date as their date
        DB::statement('UPDATE time_logs SET date = DATE(clock_in) WHERE date IS NULL');
    }

    public function down()
    {
        // No need to revert this data update
    }
}; 