<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProductImageFixSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Starting Product Image Fix Seeder...');

        // Fix combo product images
        $this->fixComboImages();
        
        // Fix food product images
        $this->fixFoodImages();
        
        // Fix drink product images
        $this->fixDrinkImages();
        
        // Fix dessert product images
        $this->fixDessertImages();

        $this->command->info('Product Image Fix Seeder completed successfully!');
    }

    private function fixComboImages()
    {
        $this->command->info('Fixing combo product images...');
        
        $comboImageUpdates = [
            'Momo Combo Plate' => 'products/combos/student-set.jpg',
            'Family Combo Feast' => 'products/combos/family-set.jpg',
            'Spicy Duo Combo' => 'products/combos/group-combo.jpg',
        ];

        foreach ($comboImageUpdates as $productName => $imagePath) {
            $product = Product::where('name', $productName)->first();
            if ($product) {
                $product->update(['image' => $imagePath]);
                $this->command->info("Updated {$productName} image to {$imagePath}");
            } else {
                $this->command->warn("Product not found: {$productName}");
            }
        }
    }

    private function fixFoodImages()
    {
        $this->command->info('Fixing food product images...');
        
        $foodImageUpdates = [
            'Classic Pork Momos' => 'products/foods/classic-pork-momos.jpg',
            'Spicy Chicken Momos' => 'products/foods/spicy-chicken-momos.jpg',
            'Veg Momos' => 'products/foods/veg-momos.jpg',
            'Cheese Corn Momos' => 'products/foods/cheese-corn-momos.jpg',
            'Paneer Momos' => 'products/foods/Paneer-momos.jpg',
            'Chilli Garlic Momos' => 'products/foods/Chilli-garlic-momos.jpg',
            'Fried Chicken Momos' => 'products/foods/fried-chicken-momos.jpg',
            'Steamed Chicken Momos' => 'products/foods/steamed-chicken-momos.jpg',
            'Tandoori Momos' => 'products/foods/tandoori-momos.jpg',
        ];

        foreach ($foodImageUpdates as $productName => $imagePath) {
            $product = Product::where('name', $productName)->first();
            if ($product) {
                $product->update(['image' => $imagePath]);
                $this->command->info("Updated {$productName} image to {$imagePath}");
            } else {
                $this->command->warn("Product not found: {$productName}");
            }
        }
    }

    private function fixDrinkImages()
    {
        $this->command->info('Fixing drink product images...');
        
        $drinkImageUpdates = [
            'Iced Coffee' => 'products/drinks/iced-coffee.jpg',
            'Mango Lassi' => 'products/drinks/mango-lassi.jpg',
            'Lemon Iced Tea' => 'products/drinks/lemon-iced-tea.jpg',
            'Hot Chocolate' => 'products/drinks/hot-chocolate.jpg',
            'Coconut Water' => 'products/drinks/coconut-water.jpg',
            'Masala Chai' => 'products/drinks/masala-chai.jpg',
            'Cold Brew' => 'products/drinks/cold-brew.jpg',
            'Matcha Latte' => 'products/drinks/matcha-latte.jpg',
            'Strawberry Smoothie' => 'products/drinks/strawberry-smoothie.jpg',
            'Mint Cooler' => 'products/drinks/mint-cooler.jpg',
        ];

        foreach ($drinkImageUpdates as $productName => $imagePath) {
            $product = Product::where('name', $productName)->first();
            if ($product) {
                $product->update(['image' => $imagePath]);
                $this->command->info("Updated {$productName} image to {$imagePath}");
            } else {
                $this->command->warn("Product not found: {$productName}");
            }
        }
    }

    private function fixDessertImages()
    {
        $this->command->info('Fixing dessert product images...');
        
        $dessertImageUpdates = [
            'Chocolate Cake' => 'products/desserts/chocolate-cake.jpg',
            'Mango Cheesecake' => 'products/desserts/mango-cheesecake.jpg',
            'Gulab Jamun' => 'products/desserts/gulab-jamun.jpg',
            'Brownie Sundae' => 'products/desserts/browine-sundae.jpg',
            'Rice Pudding' => 'products/desserts/rice-pudding.jpg',
        ];

        foreach ($dessertImageUpdates as $productName => $imagePath) {
            $product = Product::where('name', $productName)->first();
            if ($product) {
                $product->update(['image' => $imagePath]);
                $this->command->info("Updated {$productName} image to {$imagePath}");
            } else {
                $this->command->warn("Product not found: {$productName}");
            }
        }
    }
}
