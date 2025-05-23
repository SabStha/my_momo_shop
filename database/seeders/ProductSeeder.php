<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Chicken Momo', 'price' => 180.00, 'description' => 'Steamed chicken dumplings', 'image' => 'products/chicken_momo.jpg'],
            ['name' => 'Buff Momo', 'price' => 160.00, 'description' => 'Steamed buffalo dumplings', 'image' => 'products/buff_momo.jpg'],
            ['name' => 'Veg Momo', 'price' => 140.00, 'description' => 'Steamed vegetable dumplings', 'image' => 'products/veg_momo.jpg'],
            ['name' => 'Fried Momo', 'price' => 200.00, 'description' => 'Crispy fried momos', 'image' => 'products/fried_momo.jpg'],
            ['name' => 'Coke', 'price' => 60.00, 'description' => 'Chilled Coca-Cola', 'image' => 'products/coke.jpg'],
            ['name' => 'Sprite', 'price' => 60.00, 'description' => 'Chilled Sprite', 'image' => 'products/sprite.jpg'],
        ];
        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 