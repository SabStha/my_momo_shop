<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Support\Str;

class MenuDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting Menu Data Seeder...');

        // Get or create main branch
        $mainBranch = Branch::where('is_main', true)->first();
        if (!$mainBranch) {
            $mainBranch = Branch::first();
        }

        // Update existing food products with correct categories
        $this->updateFoodProducts();
        
        // Update existing drink products with correct categories
        $this->updateDrinkProducts();
        
        // Update existing dessert products
        $this->updateDessertProducts();
        
        // Update existing combo products
        $this->updateComboProducts();
        
        // Add missing products to ensure all categories have data
        $this->addMissingProducts();

        $this->command->info('Menu Data Seeder completed successfully!');
    }

    private function updateFoodProducts()
    {
        $this->command->info('Updating food products...');
        
        // Update existing food products with correct categories
        $foodUpdates = [
            'Classic Pork Momos' => 'main',
            'Spicy Chicken Momos' => 'chicken',
            'Veg Momos' => 'buff',
            'Cheese Corn Momos' => 'buff',
            'Paneer Momos' => 'buff',
            'Chilli Garlic Momos' => 'main',
            'Fried Chicken Momos' => 'chicken',
            'Steamed Chicken Momos' => 'chicken',
            'Tandoori Momos' => 'main',
        ];

        foreach ($foodUpdates as $name => $category) {
            $product = Product::where('name', $name)->where('tag', 'foods')->first();
            if ($product) {
                $product->update(['category' => $category]);
                $this->command->info("Updated {$name} category to {$category}");
            }
        }
    }

    private function updateDrinkProducts()
    {
        $this->command->info('Updating drink products...');
        
        // Update existing drink products with correct categories
        $drinkUpdates = [
            'Iced Coffee' => 'cold',
            'Mango Lassi' => 'cold',
            'Lemon Iced Tea' => 'cold',
            'Hot Chocolate' => 'hot',
            'Coconut Water' => 'cold',
            'Masala Chai' => 'hot',
            'Cold Brew' => 'cold',
            'Matcha Latte' => 'hot',
            'Strawberry Smoothie' => 'cold',
            'Mint Cooler' => 'cold',
        ];

        foreach ($drinkUpdates as $name => $category) {
            $product = Product::where('name', $name)->where('tag', 'drinks')->first();
            if ($product) {
                $product->update(['category' => $category]);
                $this->command->info("Updated {$name} category to {$category}");
            }
        }
    }

    private function updateDessertProducts()
    {
        $this->command->info('Updating dessert products...');
        
        // Update existing dessert products
        $dessertUpdates = [
            'Chocolate Cake' => 'desserts',
            'Mango Cheesecake' => 'desserts',
            'Gulab Jamun' => 'desserts',
            'Brownie Sundae' => 'desserts',
            'Rice Pudding' => 'desserts',
        ];

        foreach ($dessertUpdates as $name => $category) {
            $product = Product::where('name', $name)->where('tag', 'desserts')->first();
            if ($product) {
                $product->update(['category' => $category]);
                $this->command->info("Updated {$name} category to {$category}");
            }
        }
    }

    private function updateComboProducts()
    {
        $this->command->info('Updating combo products...');
        
        // Update existing combo products
        $comboUpdates = [
            'Momo Combo Plate' => 'combos',
            'Family Combo Feast' => 'combos',
            'Spicy Duo Combo' => 'combos',
        ];

        foreach ($comboUpdates as $name => $category) {
            $product = Product::where('name', $name)->where('tag', 'combos')->first();
            if ($product) {
                $product->update(['category' => $category]);
                $this->command->info("Updated {$name} category to {$category}");
            }
        }
    }

    private function addMissingProducts()
    {
        $this->command->info('Adding missing products...');
        
        // Add missing food products for each category
        $missingFoods = [
            'buff' => [
                ['name' => 'Mixed Vegetable Momos', 'image' => 'veg-momos.jpg', 'price' => 6.00],
                ['name' => 'Mushroom Momos', 'image' => 'veg-momos.jpg', 'price' => 6.50],
            ],
            'chicken' => [
                ['name' => 'Spicy Chicken Wings', 'image' => 'fried-chicken-momos.jpg', 'price' => 8.00],
                ['name' => 'Grilled Chicken Momos', 'image' => 'steamed-chicken-momos.jpg', 'price' => 7.50],
            ],
            'main' => [
                ['name' => 'Beef Momos', 'image' => 'classic-pork-momos.jpg', 'price' => 7.00],
                ['name' => 'Lamb Momos', 'image' => 'classic-pork-momos.jpg', 'price' => 7.50],
            ],
            'side' => [
                ['name' => 'French Fries', 'image' => 'image.png', 'price' => 3.00],
                ['name' => 'Onion Rings', 'image' => 'image.png', 'price' => 3.50],
                ['name' => 'Chicken Wings', 'image' => 'fried-chicken-momos.jpg', 'price' => 5.00],
            ],
        ];

        foreach ($missingFoods as $category => $products) {
            foreach ($products as $product) {
                $this->createProductIfNotExists($product['name'], $product['description'] ?? 'Delicious ' . strtolower($product['name']), $product['price'], $product['image'], 'foods', $category);
            }
        }

        // Add missing drink products for each category
        $missingDrinks = [
            'hot' => [
                ['name' => 'Green Tea', 'image' => 'masala-chai.jpg', 'price' => 2.50],
                ['name' => 'Coffee', 'image' => 'hot-chocolate.jpg', 'price' => 3.00],
            ],
            'cold' => [
                ['name' => 'Orange Juice', 'image' => 'mango-lassi.jpg', 'price' => 3.50],
                ['name' => 'Apple Juice', 'image' => 'mango-lassi.jpg', 'price' => 3.00],
            ],
            'boba' => [
                ['name' => 'Boba Milk Tea', 'image' => 'matcha-latte.jpg', 'price' => 4.50],
                ['name' => 'Taro Bubble Tea', 'image' => 'matcha-latte.jpg', 'price' => 4.00],
                ['name' => 'Brown Sugar Boba', 'image' => 'matcha-latte.jpg', 'price' => 4.50],
            ],
        ];

        foreach ($missingDrinks as $category => $products) {
            foreach ($products as $product) {
                $this->createProductIfNotExists($product['name'], $product['description'] ?? 'Refreshing ' . strtolower($product['name']), $product['price'], $product['image'], 'drinks', $category);
            }
        }

        // Add missing dessert products
        $missingDesserts = [
            ['name' => 'Ice Cream Sundae', 'image' => 'custom-icecream.jpg', 'price' => 4.50],
            ['name' => 'Waffles with Ice Cream', 'image' => 'waffles-icecream.jpg', 'price' => 5.00],
            ['name' => 'Chocolate Brownie', 'image' => 'browine-sundae.jpg', 'price' => 3.50],
        ];

        foreach ($missingDesserts as $product) {
            $this->createProductIfNotExists($product['name'], $product['description'] ?? 'Sweet and delicious ' . strtolower($product['name']), $product['price'], $product['image'], 'desserts', 'desserts');
        }

        // Add missing combo products
        $missingCombos = [
            ['name' => 'Student Combo', 'image' => 'student-set.jpg', 'price' => 12.99],
            ['name' => 'Office Worker Combo', 'image' => 'office-worker-set.jpg', 'price' => 15.99],
            ['name' => 'Party Combo', 'image' => 'party-set.jpg', 'price' => 29.99],
            ['name' => 'Group Combo', 'image' => 'group-combo.jpg', 'price' => 19.99],
            ['name' => 'Family Set', 'image' => 'family-set.jpg', 'price' => 24.99],
        ];

        foreach ($missingCombos as $product) {
            $this->createProductIfNotExists($product['name'], $product['description'] ?? 'Perfect combo meal with great value', $product['price'], $product['image'], 'combos', 'combos');
        }
    }

    private function createProductIfNotExists($name, $description, $price, $image, $tag, $category)
    {
        $existingProduct = Product::where('name', $name)->first();
        
        if (!$existingProduct) {
            Product::create([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => rand(20, 100),
                'is_active' => true,
                'cost_price' => $price * 0.5,
                'is_featured' => rand(0, 1),
                'image' => 'products/' . $tag . '/' . $image,
                'unit' => $this->getUnit($tag),
                'category' => $category,
                'tag' => $tag,
                'points' => $price,
                'tax_rate' => 5.00,
                'discount_rate' => rand(0, 1) ? 0.50 : 0.00,
                'code' => Str::upper(substr($name, 0, 3)) . '-' . Str::random(6),
            ]);
            $this->command->info("Created product: {$name}");
        } else {
            $this->command->info("Product already exists: {$name} (skipping)");
        }
    }

    private function getUnit($tag)
    {
        return match ($tag) {
            'foods' => 'plate',
            'drinks' => 'cup',
            'desserts' => 'piece',
            'combos' => 'set',
            default => 'item',
        };
    }
} 