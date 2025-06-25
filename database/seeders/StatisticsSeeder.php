<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\User;
use Carbon\Carbon;

class StatisticsSeeder extends Seeder
{
    public function run()
    {
        // Create sample users if they don't exist
        $users = [];
        for ($i = 1; $i <= 20; $i++) {
            $users[] = User::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name' => "Customer {$i}",
                    'password' => bcrypt('password'),
                    'phone' => '09' . str_pad(($i + 100000000), 9, '0', STR_PAD_LEFT),
                    'role' => 'customer'
                ]
            );
        }

        // Create sample momo products if they don't exist
        $momoProducts = [
            'Classic Chicken Momo',
            'Steamed Vegetable Momo',
            'Spicy Pork Momo',
            'Cheese Momo',
            'Jhol Momo',
            'Kothey Momo',
            'Fried Momo',
            'Tandoori Momo',
            'Mushroom Momo',
            'Beef Momo',
            'Shrimp Momo',
            'Paneer Momo',
            'Mixed Momo',
            'Sweet Momo',
            'Chocolate Momo'
        ];

        foreach ($momoProducts as $index => $name) {
            Product::firstOrCreate(
                ['name' => $name],
                [
                    'code' => 'MOMO-' . strtoupper(substr($name, 0, 3)) . '-' . rand(1000, 9999),
                    'description' => "Delicious {$name} made with fresh ingredients",
                    'price' => rand(8, 25) + (rand(0, 99) / 100),
                    'stock' => rand(50, 200),
                    'image' => 'default.jpg',
                    'is_featured' => rand(0, 100) < 30,
                    'is_active' => true,
                    'unit' => 'piece',
                    'category' => 'Momo',
                    'tag' => 'food',
                    'points' => 0,
                    'tax_rate' => 0,
                    'discount_rate' => 0
                ]
            );
        }

        // Create sample completed orders
        $products = Product::where('category', 'Momo')->get();
        
        for ($i = 0; $i < 50; $i++) {
            $user = $users[array_rand($users)];
            $orderDate = Carbon::now()->subDays(rand(1, 365));
            
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'status' => 'completed',
                'payment_status' => 'paid',
                'subtotal' => 0,
                'tax' => 0,
                'discount' => 0,
                'total' => 0,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Add 1-3 random products to each order
            $orderProducts = $products->random(rand(1, 3));
            $total = 0;
            
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 5);
                $price = $product->price;
                $subtotal = $quantity * $price;
                $total += $subtotal;
                
                \App\Models\OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'item_name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal
                ]);
            }
            
            $order->update([
                'subtotal' => $total,
                'total' => $total
            ]);
        }

        // Create sample product ratings
        $products = Product::where('category', 'Momo')->get();
        
        foreach ($products as $product) {
            // Create 5-15 ratings for each product
            $numRatings = rand(5, 15);
            
            for ($i = 0; $i < $numRatings; $i++) {
                $user = $users[array_rand($users)];
                $rating = rand(4, 5); // Mostly positive ratings
                
                ProductRating::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'product_id' => $product->id
                    ],
                    [
                        'rating' => $rating,
                        'review' => $this->getSampleReview($rating),
                        'created_at' => Carbon::now()->subDays(rand(1, 90))
                    ]
                );
            }
        }

        $this->command->info('Statistics sample data created successfully!');
    }

    private function getSampleReview($rating)
    {
        $reviews = [
            5 => [
                'Absolutely delicious! Best momos I\'ve ever had.',
                'Amazing taste and perfect texture. Highly recommended!',
                'Fresh ingredients and authentic flavor. Love it!',
                'Excellent quality and great value for money.',
                'Perfect for family dinner. Everyone loved it!'
            ],
            4 => [
                'Very good momos, would order again.',
                'Nice flavor and good portion size.',
                'Satisfied with the quality and taste.',
                'Good value for money, tasty momos.',
                'Enjoyed the meal, will recommend to friends.'
            ]
        ];

        $reviewList = $reviews[$rating] ?? $reviews[5];
        return $reviewList[array_rand($reviewList)];
    }
} 