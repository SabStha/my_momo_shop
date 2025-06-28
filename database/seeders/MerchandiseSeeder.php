<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Merchandise;

class MerchandiseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $merchandiseData = [
            // T-Shirts
            [
                'name' => 'MOMO SHOP T-SHIRT',
                'description' => 'Classic cotton t-shirt with our signature momo logo. Available in S, M, L, XL.',
                'price' => 24.99,
                'image' => 'momo-tshirt.jpg',
                'category' => 'tshirts',
                'model' => 'classic',
                'purchasable' => true,
                'status' => 'available'
            ],
            [
                'name' => 'CHEF\'S SPECIAL T-SHIRT',
                'description' => 'Premium chef-inspired design. Limited edition, coming soon!',
                'price' => 34.99,
                'image' => 'chef-tshirt.jpg',
                'category' => 'tshirts',
                'model' => 'premium',
                'purchasable' => false,
                'status' => 'coming_soon'
            ],
            [
                'name' => 'FAMILY PACK T-SHIRTS',
                'description' => 'Set of 4 matching t-shirts for the whole family. Perfect for family outings!',
                'price' => 89.99,
                'image' => 'family-pack.jpg',
                'category' => 'tshirts',
                'model' => 'classic',
                'purchasable' => true,
                'status' => 'available'
            ],
            [
                'name' => 'VINTAGE MOMO T-SHIRT',
                'description' => 'Retro design with classic momo artwork. Premium cotton blend.',
                'price' => 29.99,
                'image' => 'vintage-tshirt.jpg',
                'category' => 'tshirts',
                'model' => 'limited',
                'purchasable' => false,
                'status' => 'display_only'
            ],

            // Accessories
            [
                'name' => 'MOMO SHOP TOTE BAG',
                'description' => 'Eco-friendly canvas tote bag with our logo. Perfect for shopping and daily use.',
                'price' => 19.99,
                'image' => 'tote-bag.jpg',
                'category' => 'accessories',
                'model' => 'classic',
                'purchasable' => true,
                'status' => 'available'
            ],
            [
                'name' => 'CHEF\'S CAP',
                'description' => 'Professional chef\'s cap with embroidered momo logo. One size fits most.',
                'price' => 14.99,
                'image' => 'chef-cap.jpg',
                'category' => 'accessories',
                'model' => 'premium',
                'purchasable' => true,
                'status' => 'available'
            ],
            [
                'name' => 'MOMO KEYCHAIN',
                'description' => 'Cute momo-shaped keychain. Perfect gift for momo lovers!',
                'price' => 8.99,
                'image' => 'momo-keychain.jpg',
                'category' => 'accessories',
                'model' => 'classic',
                'purchasable' => false,
                'status' => 'coming_soon'
            ],
            [
                'name' => 'MOMO APRON',
                'description' => 'High-quality kitchen apron with momo design. Perfect for cooking at home.',
                'price' => 22.99,
                'image' => 'momo-apron.jpg',
                'category' => 'accessories',
                'model' => 'premium',
                'purchasable' => true,
                'status' => 'available'
            ],
            [
                'name' => 'MOMO MUG SET',
                'description' => 'Set of 4 ceramic mugs with momo designs. Microwave and dishwasher safe.',
                'price' => 39.99,
                'image' => 'momo-mugs.jpg',
                'category' => 'accessories',
                'model' => 'limited',
                'purchasable' => false,
                'status' => 'display_only'
            ],

            // Toys
            [
                'name' => 'MOMO PLUSH TOY',
                'description' => 'Soft and cuddly momo plush toy. Perfect for kids and momo lovers of all ages!',
                'price' => 16.99,
                'image' => 'momo-plush.jpg',
                'category' => 'toys',
                'model' => 'classic',
                'purchasable' => true,
                'status' => 'available'
            ],
            [
                'name' => 'MOMO PUZZLE SET',
                'description' => '100-piece puzzle featuring our delicious momos. Great for family fun!',
                'price' => 12.99,
                'image' => 'momo-puzzle.jpg',
                'category' => 'toys',
                'model' => 'classic',
                'purchasable' => true,
                'status' => 'available'
            ],
            [
                'name' => 'MOMO COLORING BOOK',
                'description' => 'Fun coloring book with momo-themed pages. Includes crayons!',
                'price' => 9.99,
                'image' => 'momo-coloring.jpg',
                'category' => 'toys',
                'model' => 'premium',
                'purchasable' => false,
                'status' => 'coming_soon'
            ],
            [
                'name' => 'MOMO BUILDING BLOCKS',
                'description' => 'Educational building blocks with momo shapes. Safe for toddlers 3+.',
                'price' => 24.99,
                'image' => 'momo-blocks.jpg',
                'category' => 'toys',
                'model' => 'premium',
                'purchasable' => true,
                'status' => 'available'
            ],
            [
                'name' => 'MOMO STUFFED ANIMAL SET',
                'description' => 'Set of 3 different momo stuffed animals. Perfect for collection!',
                'price' => 34.99,
                'image' => 'momo-stuffed-set.jpg',
                'category' => 'toys',
                'model' => 'limited',
                'purchasable' => false,
                'status' => 'display_only'
            ],

            // Limited Offers
            [
                'name' => 'LIMITED EDITION MOMO HOODIE',
                'description' => 'Exclusive hoodie with special momo embroidery. Only 50 pieces available!',
                'price' => 49.99,
                'image' => 'limited-hoodie.jpg',
                'category' => 'limited',
                'model' => 'limited',
                'purchasable' => true,
                'status' => 'limited',
                'stock' => 23,
                'badge' => 'LIMITED TIME!',
                'badge_color' => 'red'
            ],
            [
                'name' => 'HOLIDAY MOMO ORNAMENT SET',
                'description' => 'Festive momo ornaments for your tree. Perfect holiday gift!',
                'price' => 29.99,
                'image' => 'holiday-ornaments.jpg',
                'category' => 'limited',
                'model' => 'limited',
                'purchasable' => true,
                'status' => 'holiday',
                'badge' => 'HOLIDAY SPECIAL',
                'badge_color' => 'green'
            ],
            [
                'name' => 'SIGNATURE CHEF\'S JACKET',
                'description' => 'Professional chef jacket signed by our head chef. Ultra-limited edition!',
                'price' => 89.99,
                'image' => 'chef-jacket.jpg',
                'category' => 'limited',
                'model' => 'limited',
                'purchasable' => true,
                'status' => 'exclusive',
                'stock' => 5,
                'badge' => 'EXCLUSIVE',
                'badge_color' => 'purple'
            ],
            [
                'name' => 'MOMO COOKBOOK',
                'description' => 'Complete guide to making authentic momos at home. Pre-order now!',
                'price' => 34.99,
                'image' => 'momo-cookbook.jpg',
                'category' => 'limited',
                'model' => 'premium',
                'purchasable' => false,
                'status' => 'pre_order',
                'badge' => 'PRE-ORDER',
                'badge_color' => 'orange'
            ],
            [
                'name' => 'CHARITY BUNDLE PACK',
                'description' => 'Complete merchandise set. 100% of profits go to dog shelters!',
                'price' => 99.99,
                'image' => 'charity-bundle.jpg',
                'category' => 'limited',
                'model' => 'limited',
                'purchasable' => true,
                'status' => 'charity',
                'badge' => 'CHARITY BUNDLE',
                'badge_color' => 'blue'
            ]
        ];

        foreach ($merchandiseData as $item) {
            // Check if merchandise already exists by name
            $existingMerchandise = Merchandise::where('name', $item['name'])->first();
            
            if (!$existingMerchandise) {
                Merchandise::create($item);
                $this->command->info("Created merchandise: {$item['name']}");
            } else {
                $this->command->info("Merchandise already exists: {$item['name']} (skipping)");
            }
        }
    }
}
