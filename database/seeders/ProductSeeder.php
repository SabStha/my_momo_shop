<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $branches = Branch::all();
        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run BranchSeeder first.');
            return;
        }

        $this->seedProducts($branches, [
            'foods' => [
                ['name' => 'Classic Pork Momos', 'image' => 'classic-pork-momos.jpg'],
                ['name' => 'Spicy Chicken Momos', 'image' => 'spicy-chicken-momos.jpg'],
                ['name' => 'Veg Momos', 'image' => 'veg-momos.jpg'],
                ['name' => 'Cheese Corn Momos', 'image' => 'cheese-corn-momos.jpg'],
                ['name' => 'Paneer Momos', 'image' => 'Paneer-momos.jpg'],
                ['name' => 'Chilli Garlic Momos', 'image' => 'Chilli-garlic-momos.jpg'],
                ['name' => 'Fried Chicken Momos', 'image' => 'fried-chicken-momos.jpg'],
                ['name' => 'Steamed Chicken Momos', 'image' => 'steamed-chicken-momos.jpg'],
                ['name' => 'Tandoori Momos', 'image' => 'tandoori-momos.jpg'],
            ],
            'drinks' => [
                ['name' => 'Iced Coffee', 'image' => 'iced-coffee.jpg'],
                ['name' => 'Mango Lassi', 'image' => 'mango-lassi.jpg'],
                ['name' => 'Lemon Iced Tea', 'image' => 'lemon-iced-tea.jpg'],
                ['name' => 'Hot Chocolate', 'image' => 'hot-chocolate.jpg'],
                ['name' => 'Coconut Water', 'image' => 'coconut-water.jpg'],
                ['name' => 'Masala Chai', 'image' => 'masala-chai.jpg'],
                ['name' => 'Cold Brew', 'image' => 'cold-brew.jpg'],
                ['name' => 'Matcha Latte', 'image' => 'matcha-latte.jpg'],
                ['name' => 'Strawberry Smoothie', 'image' => 'strawberry-smoothie.jpg'],
                ['name' => 'Mint Cooler', 'image' => 'mint-cooler.jpg'],
            ],
            'desserts' => [
                ['name' => 'Chocolate Cake', 'image' => 'chocolate-cake.jpg'],
                ['name' => 'Mango Cheesecake', 'image' => 'mango-cheesecake.jpg'],
                ['name' => 'Gulab Jamun', 'image' => 'gulab-jamun.jpg'],
                ['name' => 'Brownie Sundae', 'image' => 'browine-sundae.jpg'],
                ['name' => 'Rice Pudding', 'image' => 'rice-pudding.jpg'],
            ],
        ]);

        // COMBOS
        $combos = [
            [
                'name' => 'Momo Combo Plate',
                'description' => 'A perfect pair of momos and drink',
                'price' => 9.99,
                'image' => 'products/combos/momo_combo_plate.jpg',
                'unit' => 'plate',
                'category' => 'Combos',
                'tag' => 'combos',
            ],
            [
                'name' => 'Family Combo Feast',
                'description' => 'A full-family momo and drinks set',
                'price' => 24.99,
                'image' => 'products/combos/family_combo_feast.jpg',
                'unit' => 'set',
                'category' => 'Combos',
                'tag' => 'combos',
            ],
            [
                'name' => 'Spicy Duo Combo',
                'description' => 'Spicy momos and sauce combo for two',
                'price' => 14.99,
                'image' => 'products/combos/spicy_duo_combo.jpg',
                'unit' => 'box',
                'category' => 'Combos',
                'tag' => 'combos',
            ],
        ];

        foreach ($combos as $combo) {
            Product::create([
                'name' => $combo['name'],
                'description' => $combo['description'],
                'price' => $combo['price'],
                'stock' => rand(10, 50),
                'is_active' => true,
                'cost_price' => $combo['price'] * 0.5,
                'is_featured' => rand(0, 1),
                'image' => $combo['image'],
                'unit' => $combo['unit'],
                'category' => $combo['category'],
                'tag' => $combo['tag'],
                'points' => $combo['price'],
                'tax_rate' => 5.00,
                'discount_rate' => rand(0, 1) ? 1.00 : 0.00,
                'code' => Str::upper(substr($combo['name'], 0, 3)) . '-' . Str::random(6),
            ]);
        }
    }

    private function seedProducts($branches, array $groupedItems)
    {
        foreach ($groupedItems as $folder => $products) {
            foreach ($products as $item) {
                Product::create([
                    'name' => $item['name'],
                    'description' => $this->getDescription($folder),
                    'price' => $this->getPrice($folder),
                    'stock' => rand(20, 100),
                    'is_active' => true,
                    'cost_price' => $this->getPrice($folder) * 0.5,
                    'is_featured' => rand(0, 1),
                    'image' => 'products/' . $folder . '/' . $item['image'],
                    'unit' => $this->getUnit($folder),
                    'category' => ucfirst($folder),
                    'tag' => $folder,
                    'points' => $this->getPrice($folder),
                    'tax_rate' => 5.00,
                    'discount_rate' => rand(0, 1) ? 0.50 : 0.00,
                    'code' => Str::upper(substr($item['name'], 0, 3)) . '-' . Str::random(6),
                ]);
            }
        }
    }

    private function getDescription($folder)
    {
        return match ($folder) {
            'foods' => 'Handmade dumplings',
            'drinks' => 'Chilled or hot drink',
            'desserts' => 'Sweet and satisfying dessert',
            default => 'Delicious item',
        };
    }

    private function getPrice($folder)
    {
        return match ($folder) {
            'foods' => 6.00,
            'drinks' => 3.00,
            'desserts' => 5.00,
            default => 5.00,
        };
    }

    private function getUnit($folder)
    {
        return match ($folder) {
            'foods' => 'plate',
            'drinks' => 'cup',
            'desserts' => 'piece',
            default => 'item',
        };
    }
}
