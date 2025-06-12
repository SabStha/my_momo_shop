<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterStatusEnumOnReferralsTable extends Migration
{
    public function up()
    {
        try {
            // Modify the status enum directly
            DB::statement("ALTER TABLE referrals MODIFY COLUMN status ENUM('signed_up', 'ordered', 'completed') DEFAULT 'signed_up'");
        } catch (\Exception $e) {
            // If the table doesn't exist yet, that's fine - it will be created with the correct enum in the create table migration
            if (!Schema::hasTable('referrals')) {
                return;
            }
            throw $e;
        }
    }

    public function down()
    {
        try {
            // Revert the status enum
            DB::statement("ALTER TABLE referrals MODIFY COLUMN status ENUM('signed_up', 'ordered') DEFAULT 'signed_up'");
        } catch (\Exception $e) {
            // If the table doesn't exist, that's fine
            if (!Schema::hasTable('referrals')) {
                return;
            }
            throw $e;
        }
    }
}
