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
                'name' => 'Coffee',
                'description' => 'Hot coffee',
                'price' => 2.50,
                'stock' => 100,
                'is_active' => true,
                'cost_price' => 1.00,
                'is_menu_highlight' => true,
                'image' => 'products/coffee.jpg'
            ],
            [
                'name' => 'Tea',
                'description' => 'Hot tea',
                'price' => 2.00,
                'stock' => 100,
                'is_active' => true,
                'cost_price' => 0.80,
                'is_menu_highlight' => false,
                'image' => 'products/tea.jpg'
            ],
            [
                'name' => 'Sandwich',
                'description' => 'Fresh sandwich',
                'price' => 5.00,
                'stock' => 50,
                'is_active' => true,
                'cost_price' => 2.50,
                'is_menu_highlight' => true,
                'image' => 'products/sandwich.jpg'
            ],
            [
                'name' => 'Cake',
                'description' => 'Fresh cake',
                'price' => 4.00,
                'stock' => 30,
                'is_active' => true,
                'cost_price' => 2.00,
                'is_menu_highlight' => false,
                'image' => 'products/cake.jpg'
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
} 