<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // First, modify the column to accommodate our wallet number format
        Schema::table('wallets', function (Blueprint $table) {
            $table->string('wallet_number', 19)->change(); // 16 chars + 3 hyphens
        });

        // Generate wallet numbers for empty wallet numbers
        DB::table('wallets')
            ->where('wallet_number', '')
            ->orWhereNull('wallet_number')
            ->orderBy('id')
            ->each(function ($wallet) {
                DB::table('wallets')
                    ->where('id', $wallet->id)
                    ->update(['wallet_number' => $this->generateWalletNumber()]);
            });

        // Add unique constraint to wallet_number
        Schema::table('wallets', function (Blueprint $table) {
            $table->unique('wallet_number');
        });
    }

    public function down()
    {
        // No need to do anything in down() as we don't want to remove wallet numbers
    }

    private function generateWalletNumber()
    {
        do {
            // Generate a 16-character wallet number with format: XXXX-XXXX-XXXX-XXXX
            $number = strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4));
        } while (DB::table('wallets')->where('wallet_number', $number)->exists());

        return $number;
    }
}; 