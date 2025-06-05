<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Drop the trigger that automatically creates wallets
        DB::unprepared('DROP TRIGGER IF EXISTS create_user_wallet');
    }

    public function down()
    {
        // Recreate the trigger if we need to rollback
        DB::unprepared('
            CREATE TRIGGER create_user_wallet
            AFTER INSERT ON users
            FOR EACH ROW
            BEGIN
                INSERT INTO wallets (user_id, balance, created_at, updated_at)
                VALUES (NEW.id, 0, NOW(), NOW());
            END
        ');
    }
};
