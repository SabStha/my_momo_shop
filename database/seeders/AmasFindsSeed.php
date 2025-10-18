<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AmasFindsSeed extends Seeder
{
    /**
     * Seed Ama's Finds with special earned items
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Amako Limited Edition Tote Bag',
                'description' => '🎁 Exclusive canvas tote bag with Amako Momo branding. Perfect for carrying your favorite momos! **Earn this by purchasing the Couple Combo Set.**',
                'price' => 0.00, // Not purchasable, earned only
                'image' => 'merchandise/tote-bag-limited.jpg',
                'category' => 'accessories',
                'model' => 'limited-edition',
                'purchasable' => false, // Cannot be bought
                'status' => 'exclusive', // Exclusive reward
                'stock' => null, // Unlimited for earned items
                'badge' => '🏆 Earned by Couple Set',
                'badge_color' => '#EF4444', // Red badge
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Amako Kids Plush Toy',
                'description' => '🧸 Adorable momo-shaped plush toy for kids. Soft, huggable, and exclusively available as a reward! **Earn this by purchasing the Kids Combo Set.**',
                'price' => 0.00, // Not purchasable, earned only
                'image' => 'merchandise/plush-toy-momo.jpg',
                'category' => 'toys',
                'model' => 'exclusive',
                'purchasable' => false, // Cannot be bought
                'status' => 'exclusive', // Exclusive reward
                'stock' => null, // Unlimited for earned items
                'badge' => '🎁 Earned by Kids Set',
                'badge_color' => '#F59E0B', // Orange/yellow badge
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($items as $item) {
            DB::table('merchandises')->updateOrInsert(
                ['name' => $item['name']], // Match by name
                $item
            );
        }

        echo "✅ Ama's Finds: Added 2 exclusive earned items\n";
        echo "   • Amako Limited Edition Tote Bag (earned by Couple Set)\n";
        echo "   • Amako Kids Plush Toy (earned by Kids Set)\n";
    }
}

