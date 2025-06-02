<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        // Get all users without wallets
        $users = DB::table('users')
            ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
            ->whereNull('wallets.id')
            ->select('users.id')
            ->get();

        // Create wallets for users without one
        foreach ($users as $user) {
            DB::table('wallets')->insert([
                'user_id' => $user->id,
                'balance' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down() {
        // No need to do anything in down() as we don't want to remove wallets
    }
}; 