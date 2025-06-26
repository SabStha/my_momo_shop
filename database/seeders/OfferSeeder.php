<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Offer;
use Carbon\Carbon;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $offers = [
            [
                'title' => 'First Order Discount',
                'description' => 'New customers get 20% off their first order. Use code: WELCOME20',
                'discount' => 20.00,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(3),
                'is_active' => true,
                'code' => 'WELCOME20',
                'min_purchase' => 10.00,
                'max_discount' => 50.00,
            ],
            [
                'title' => 'Combo Deal',
                'description' => 'Order any 2 momo dishes and get 1 free! Valid on all momo varieties.',
                'discount' => 33.33,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(1),
                'is_active' => true,
                'code' => 'BOGO2024',
                'min_purchase' => 15.00,
                'max_discount' => 25.00,
            ],
            [
                'title' => 'Weekend Special',
                'description' => 'Special family combo with 15% discount. Perfect for weekend gatherings!',
                'discount' => 15.00,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addDays(7),
                'is_active' => true,
                'code' => 'WEEKEND15',
                'min_purchase' => 25.00,
                'max_discount' => 30.00,
            ],
            [
                'title' => 'Loyalty Rewards',
                'description' => 'Join our loyalty program. Earn 1 point per $1 spent. 100 points = $5 off!',
                'discount' => 5.00,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addYears(1),
                'is_active' => true,
                'code' => 'LOYALTY100',
                'min_purchase' => 100.00,
                'max_discount' => 5.00,
            ],
            [
                'title' => 'Bulk Discount',
                'description' => 'Order 10+ items and get 25% off! Perfect for events and parties.',
                'discount' => 25.00,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addMonths(6),
                'is_active' => true,
                'code' => 'BULK25',
                'min_purchase' => 50.00,
                'max_discount' => 100.00,
            ],
            [
                'title' => 'Flash Sale',
                'description' => 'Limited time! 30% off all steamed momos. Ends in 2 hours!',
                'discount' => 30.00,
                'valid_from' => Carbon::now(),
                'valid_until' => Carbon::now()->addHours(2),
                'is_active' => true,
                'code' => 'FLASH30',
                'min_purchase' => 20.00,
                'max_discount' => 40.00,
            ],
        ];

        foreach ($offers as $offer) {
            Offer::updateOrCreate(
                ['code' => $offer['code']],
                $offer
            );
        }
    }
} 