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
                'name' => 'Smartphone X',
                'description' => 'Latest smartphone with amazing features',
                'price' => 699.99,
                'stock' => 50,
                'image' => 'products/smartphone.jpg',
            ],
            [
                'name' => 'Laptop Pro',
                'description' => 'Powerful laptop for professionals',
                'price' => 1299.99,
                'stock' => 30,
                'image' => 'products/laptop.jpg',
            ],
            [
                'name' => 'Wireless Earbuds',
                'description' => 'High-quality wireless earbuds',
                'price' => 149.99,
                'stock' => 100,
                'image' => 'products/earbuds.jpg',
            ],
            [
                'name' => 'Smart Watch',
                'description' => 'Feature-rich smartwatch',
                'price' => 249.99,
                'stock' => 75,
                'image' => 'products/smartwatch.jpg',
            ],
            [
                'name' => 'Bluetooth Speaker',
                'description' => 'Portable bluetooth speaker',
                'price' => 79.99,
                'stock' => 60,
                'image' => 'products/speaker.jpg',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }

        $this->command->info('Dummy products created successfully!');
    }
} 