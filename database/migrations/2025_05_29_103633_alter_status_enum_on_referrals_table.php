<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterStatusEnumOnReferralsTable extends Migration
{
    public function up()
    {
        // For MySQL, use raw SQL to modify ENUM
        DB::statement("ALTER TABLE referrals MODIFY COLUMN status ENUM('pending', 'used', 'expired', 'signed_up', 'ordered') DEFAULT 'pending'");
    }

    public function down()
    {
        // Revert to the original ENUM values if needed
        DB::statement("ALTER TABLE referrals MODIFY COLUMN status ENUM('pending', 'used', 'expired') DEFAULT 'pending'");
    }
}
