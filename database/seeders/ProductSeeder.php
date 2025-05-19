<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $products = [
            [
                'name' => 'Steamed Chicken Momo',
                'description' => 'Delicious steamed chicken dumplings with our special sauce.',
                'price' => 12.99,
                'image' => 'images/momos/steamed-chicken.jpg',
                'category' => 'Chicken'
            ],
            [
                'name' => 'Steamed Vegetable Momo',
                'description' => 'Healthy vegetable dumplings steamed to perfection.',
                'price' => 10.99,
                'image' => 'images/momos/steamed-veg.jpg',
                'category' => 'Vegetarian'
            ],
            [
                'name' => 'Fried Pork Momo',
                'description' => 'Crispy fried pork dumplings with a spicy kick.',
                'price' => 13.99,
                'image' => 'images/momos/fried-pork.jpg',
                'category' => 'Pork'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 