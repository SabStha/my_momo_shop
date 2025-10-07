<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FindsCategory;

class FindsCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'key' => 'buyable',
                'label' => 'BUY',
                'icon' => '🛒',
                'description' => 'Items you can purchase directly',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'key' => 'unlockable',
                'label' => 'EARN',
                'icon' => '🎁',
                'description' => 'Items you can earn by completing meals',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'key' => 'tshirts',
                'label' => 'SHIRT',
                'icon' => '👕',
                'description' => 'Exclusive t-shirts and apparel',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'key' => 'accessories',
                'label' => 'GIFT',
                'icon' => '🎁',
                'description' => 'Accessories and gift items',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'key' => 'toys',
                'label' => 'TOYS',
                'icon' => '🧸',
                'description' => 'Fun toys and collectibles',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'key' => 'limited',
                'label' => 'LIM',
                'icon' => '⚡',
                'description' => 'Limited edition items',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $categoryData) {
            FindsCategory::updateOrCreate(
                ['key' => $categoryData['key']],
                $categoryData
            );
        }
    }
}
