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
                ['name' => 'Classic Pork Momos', 'image' => 'products/foods/classic-pork-momos.jpg'],
                ['name' => 'Spicy Chicken Momos', 'image' => 'products/foods/spicy-chicken-momos.jpg'],
                ['name' => 'Veg Momos', 'image' => 'products/foods/veg-momos.jpg'],
                ['name' => 'Cheese Corn Momos', 'image' => 'products/foods/cheese-corn-momos.jpg'],
                ['name' => 'Paneer Momos', 'image' => 'products/foods/Paneer-momos.jpg'],
                ['name' => 'Chilli Garlic Momos', 'image' => 'products/foods/Chilli-garlic-momos.jpg'],
                ['name' => 'Fried Chicken Momos', 'image' => 'products/foods/fried-chicken-momos.jpg'],
                ['name' => 'Steamed Chicken Momos', 'image' => 'products/foods/steamed-chicken-momos.jpg'],
                ['name' => 'Tandoori Momos', 'image' => 'products/foods/tandoori-momos.jpg'],
            ],
            'drinks' => [
                ['name' => 'Iced Coffee', 'image' => 'products/drinks/iced-coffee.jpg'],
                ['name' => 'Mango Lassi', 'image' => 'products/drinks/mango-lassi.jpg'],
                ['name' => 'Lemon Iced Tea', 'image' => 'products/drinks/lemon-iced-tea.jpg'],
                ['name' => 'Hot Chocolate', 'image' => 'products/drinks/hot-chocolate.jpg'],
                ['name' => 'Coconut Water', 'image' => 'products/drinks/coconut-water.jpg'],
                ['name' => 'Masala Chai', 'image' => 'products/drinks/masala-chai.jpg'],
                ['name' => 'Cold Brew', 'image' => 'products/drinks/cold-brew.jpg'],
                ['name' => 'Matcha Latte', 'image' => 'products/drinks/matcha-latte.jpg'],
                ['name' => 'Strawberry Smoothie', 'image' => 'products/drinks/strawberry-smoothie.jpg'],
                ['name' => 'Mint Cooler', 'image' => 'products/drinks/mint-cooler.jpg'],
            ],
            'desserts' => [
                ['name' => 'Chocolate Cake', 'image' => 'products/desserts/chocolate-cake.jpg'],
                ['name' => 'Mango Cheesecake', 'image' => 'products/desserts/mango-cheesecake.jpg'],
                ['name' => 'Gulab Jamun', 'image' => 'products/desserts/gulab-jamun.jpg'],
                ['name' => 'Brownie Sundae', 'image' => 'products/desserts/browine-sundae.jpg'],
                ['name' => 'Rice Pudding', 'image' => 'products/desserts/rice-pudding.jpg'],
            ],
        ]);

        // COMBOS
        $combos = [
            [
                'name' => 'Momo Combo Plate',
                'description' => 'A perfect pair of momos and drink',
                'price' => 9.99,
                'image' => 'products/combos/student-set.jpg',
                'unit' => 'plate',
                'category' => 'Combos',
                'tag' => 'combos',
            ],
            [
                'name' => 'Family Combo Feast',
                'description' => 'A full-family momo and drinks set',
                'price' => 24.99,
                'image' => 'products/combos/family-set.jpg',
                'unit' => 'set',
                'category' => 'Combos',
                'tag' => 'combos',
            ],
            [
                'name' => 'Spicy Duo Combo',
                'description' => 'Spicy momos and sauce combo for two',
                'price' => 14.99,
                'image' => 'products/combos/group-combo.jpg',
                'unit' => 'box',
                'category' => 'Combos',
                'tag' => 'combos',
            ],
        ];

        foreach ($combos as $combo) {
            // Check if combo already exists by name
            $existingCombo = Product::where('name', $combo['name'])->first();
            
            if (!$existingCombo) {
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
                $this->command->info("Created combo: {$combo['name']}");
            } else {
                $this->command->info("Combo already exists: {$combo['name']} (skipping)");
            }
        }
    }

    private function seedProducts($branches, array $groupedItems)
    {
        foreach ($groupedItems as $folder => $products) {
            foreach ($products as $item) {
                // Check if product already exists by name
                $existingProduct = Product::where('name', $item['name'])->first();
                
                if (!$existingProduct) {
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
                    $this->command->info("Created product: {$item['name']}");
                } else {
                    $this->command->info("Product already exists: {$item['name']} (skipping)");
                }
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
