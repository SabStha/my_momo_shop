<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run()
    {
        Coupon::create([
            'code' => 'WELCOME10',
            'type' => 'percent',
            'amount' => 10,
            'usage_limit' => 100,
            'user_limit' => 1,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(1),
            'campaign_name' => 'Welcome Discount',
            'shop_only' => false
        ]);

        Coupon::create([
            'code' => 'SAVE20',
            'type' => 'fixed',
            'amount' => 20,
            'usage_limit' => 50,
            'user_limit' => 1,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(1),
            'campaign_name' => 'Save $20',
            'shop_only' => false
        ]);
    }
} 