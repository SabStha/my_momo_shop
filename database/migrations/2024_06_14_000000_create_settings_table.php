<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default referral settings
        DB::table('settings')->insert([
            [
                'key' => 'referral_welcome_bonus',
                'value' => '50',
                'description' => 'Amount (Rs.) given to user when they register with a referral code',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'referral_first_order_bonus',
                'value' => '30',
                'description' => 'Amount (Rs.) given to user for their first order',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'referral_subsequent_order_bonus',
                'value' => '10',
                'description' => 'Amount (Rs.) given to user for each subsequent order',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'creator_referral_bonus',
                'value' => '10',
                'description' => 'Points given to creator when someone uses their referral code',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'creator_first_order_bonus',
                'value' => '5',
                'description' => 'Points given to creator for referred user\'s first order',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'creator_subsequent_order_bonus',
                'value' => '5',
                'description' => 'Points given to creator for each subsequent order',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'max_referral_orders',
                'value' => '10',
                'description' => 'Maximum number of orders for which bonuses are given',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}; 