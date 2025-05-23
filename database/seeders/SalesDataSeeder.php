<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Faker\Factory as Faker;

class SalesDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Create some test users if they don't exist
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $users[] = User::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name' => $faker->name,
                    'password' => bcrypt('password'),
                ]
            );
        }

        // Create some products if they don't exist
        $products = [];
        $productNames = [
            'Classic Momo', 'Chicken Momo', 'Veg Momo', 'Cheese Momo',
            'Spicy Momo', 'Steamed Momo', 'Fried Momo', 'Jhol Momo',
            'Kothey Momo', 'C Momo'
        ];

        foreach ($productNames as $name) {
            $products[] = Product::firstOrCreate(
                ['name' => $name],
                [
                    'description' => $faker->sentence,
                    'price' => $faker->randomFloat(2, 5, 20),
                    'stock' => $faker->numberBetween(50, 200),
                    'image' => 'default.jpg',
                    'is_featured' => $faker->boolean(20)
                ]
            );
        }

        // Generate orders for the last 60 days
        $startDate = Carbon::now()->subDays(60);
        $endDate = Carbon::now();

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            // Generate 1-5 orders per day
            $ordersPerDay = $faker->numberBetween(1, 5);
            
            for ($i = 0; $i < $ordersPerDay; $i++) {
                $order = Order::create([
                    'user_id' => $faker->randomElement($users)->id,
                    'total_amount' => 0, // Will be calculated after adding items
                    'status' => $faker->randomElement(['completed', 'completed', 'completed', 'cancelled']), // 75% completed
                    'shipping_address' => $faker->address,
                    'billing_address' => $faker->address,
                    'payment_method' => $faker->randomElement(['credit_card', 'cash', 'online']),
                    'payment_status' => 'paid',
                    'created_at' => $date->copy()->addHours($faker->numberBetween(9, 21)),
                ]);

                // Add 1-4 items to each order
                $totalAmount = 0;
                $orderItems = $faker->numberBetween(1, 4);
                
                for ($j = 0; $j < $orderItems; $j++) {
                    $product = $faker->randomElement($products);
                    $quantity = $faker->numberBetween(1, 5);
                    $price = $product->price;
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'item_name'  => $product->name, // ✅ Fix
                        'subtotal'   => $price * $quantity, // ✅ Optional but important if subtotal is not nullable
                    ]);

                    $totalAmount += $price * $quantity;
                }

                // Update order total
                $order->update(['total_amount' => $totalAmount]);
            }
        }
    }
} 