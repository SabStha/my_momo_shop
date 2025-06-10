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
        // Get all branches
        $branches = Branch::all();
        if ($branches->isEmpty()) {
            $this->command->error('No branches found. Please run BranchSeeder first.');
            return;
        }

        $products = [
            [
                'name' => 'Coffee',
                'description' => 'Hot coffee',
                'price' => 2.50,
                'stock' => 100,
                'is_active' => true,
                'cost_price' => 1.00,
                'is_featured' => true,
                'image' => 'products/coffee.jpg',
                'unit' => 'cup',
                'category' => 'Beverages',
                'tag' => 'hot-drinks',
                'points' => 2.50,
                'tax_rate' => 5.00,
                'discount_rate' => 0.00
            ],
            [
                'name' => 'Tea',
                'description' => 'Hot tea',
                'price' => 2.00,
                'stock' => 100,
                'is_active' => true,
                'cost_price' => 0.80,
                'is_featured' => false,
                'image' => 'products/tea.jpg',
                'unit' => 'cup',
                'category' => 'Beverages',
                'tag' => 'hot-drinks',
                'points' => 2.00,
                'tax_rate' => 5.00,
                'discount_rate' => 0.00
            ],
            [
                'name' => 'Sandwich',
                'description' => 'Fresh sandwich',
                'price' => 5.00,
                'stock' => 50,
                'is_active' => true,
                'cost_price' => 2.50,
                'is_featured' => true,
                'image' => 'products/sandwich.jpg',
                'unit' => 'piece',
                'category' => 'Food',
                'tag' => 'snacks',
                'points' => 5.00,
                'tax_rate' => 5.00,
                'discount_rate' => 0.00
            ],
            [
                'name' => 'Cake',
                'description' => 'Fresh cake',
                'price' => 4.00,
                'stock' => 30,
                'is_active' => true,
                'cost_price' => 2.00,
                'is_featured' => false,
                'image' => 'products/cake.jpg',
                'unit' => 'piece',
                'category' => 'Desserts',
                'tag' => 'sweets',
                'points' => 4.00,
                'tax_rate' => 5.00,
                'discount_rate' => 0.00
            ]
        ];

        // Create products for each branch
        foreach ($branches as $branch) {
            foreach ($products as $product) {
                // Generate a unique code for each product in each branch
                $code = Str::upper(substr($product['name'], 0, 3)) . '-' . $branch->code . '-' . Str::random(4);
                
                Product::create([
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'is_active' => $product['is_active'],
                    'cost_price' => $product['cost_price'],
                    'is_featured' => $product['is_featured'],
                    'image' => $product['image'],
                    'unit' => $product['unit'],
                    'category' => $product['category'],
                    'tag' => $product['tag'],
                    'points' => $product['points'],
                    'tax_rate' => $product['tax_rate'],
                    'discount_rate' => $product['discount_rate'],
                    'branch_id' => $branch->id,
                    'code' => $code
                ]);
            }
        }
    }
} 