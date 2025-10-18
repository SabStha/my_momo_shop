<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FindsCategoriesSeeder extends Seeder
{
    /**
     * Seed finds categories
     */
    public function run(): void
    {
        $categories = [
            [
                'key' => 'buyable',
                'label' => 'BUY',
                'icon' => '🛒',
                'description' => 'Items you can purchase directly',
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'accessories',
                'label' => 'ACCESSORIES',
                'icon' => '👜',
                'description' => 'Bags, wallets, and more',
                'sort_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'toys',
                'label' => 'TOYS',
                'icon' => '🧸',
                'description' => 'Fun collectibles and plush toys',
                'sort_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tshirts',
                'label' => 'APPAREL',
                'icon' => '👕',
                'description' => 'T-shirts and clothing',
                'sort_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'limited',
                'label' => 'LIMITED',
                'icon' => '⭐',
                'description' => 'Limited edition items',
                'sort_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'unlockable',
                'label' => 'EARN',
                'icon' => '🏆',
                'description' => 'Exclusive rewards you can earn',
                'sort_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $category) {
            DB::table('finds_categories')->updateOrInsert(
                ['key' => $category['key']],
                $category
            );
        }

        echo "✅ Finds Categories: Added 6 categories\n";
        echo "   • 🛒 BUY (buyable items)\n";
        echo "   • 👜 ACCESSORIES\n";
        echo "   • 🧸 TOYS\n";
        echo "   • 👕 APPAREL\n";
        echo "   • ⭐ LIMITED\n";
        echo "   • 🏆 EARN (for unlockable items)\n";
    }
}

