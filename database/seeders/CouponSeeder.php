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
            'type' => 'percentage',
            'value' => 10,
            'max_uses' => 100,
            'min_order_amount' => 0,
            'expires_at' => now()->addMonths(1),
            'active' => true
        ]);

        Coupon::create([
            'code' => 'SAVE20',
            'type' => 'fixed',
            'value' => 20,
            'max_uses' => 50,
            'min_order_amount' => 0,
            'expires_at' => now()->addMonths(1),
            'active' => true
        ]);
    }
} 