<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE referrals MODIFY COLUMN status ENUM('pending', 'used', 'expired', 'signed_up', 'ordered') DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE referrals MODIFY COLUMN status ENUM('pending', 'used', 'expired') DEFAULT 'pending'");
    }
}; 