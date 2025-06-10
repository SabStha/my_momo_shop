<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run()
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome Discount',
                'description' => 'Get 10% off on your first order',
                'type' => 'percentage',
                'value' => 10,
                'max_uses' => 100,
                'min_order_amount' => 0,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(1),
                'is_active' => true
            ],
            [
                'code' => 'SAVE20',
                'name' => 'Save 20',
                'description' => 'Save $20 on orders over $100',
                'type' => 'fixed',
                'value' => 20,
                'max_uses' => 50,
                'min_order_amount' => 100,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(1),
                'is_active' => true
            ]
        ];

        foreach ($coupons as $couponData) {
            Coupon::firstOrCreate(
                ['code' => $couponData['code']],
                $couponData
            );
        }
    }
} 