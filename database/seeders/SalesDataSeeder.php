<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class SalesDataSeeder extends Seeder
{
    public function run()
    {
        // Create some test users if they don't exist
        $users = [];
        $names = ['John Doe', 'Jane Smith', 'Bob Johnson', 'Alice Brown', 'Charlie Wilson'];
        for ($i = 0; $i < 5; $i++) {
            $users[] = User::firstOrCreate(
                ['email' => "customer{$i}@example.com"],
                [
                    'name' => $names[$i],
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

        $descriptions = [
            'Traditional steamed dumplings filled with spiced meat',
            'Juicy chicken-filled dumplings with herbs and spices',
            'Fresh vegetables and herbs wrapped in delicate dough',
            'Melted cheese and herbs in a soft dumpling wrapper',
            'Extra spicy dumplings for the adventurous',
            'Light and fluffy steamed dumplings',
            'Crispy fried dumplings with a golden crust',
            'Dumplings served in a flavorful broth',
            'Pan-fried dumplings with a crispy bottom',
            'Special dumplings with a unique filling'
        ];

        foreach ($productNames as $index => $name) {
            $products[] = Product::firstOrCreate(
                ['name' => $name],
                [
                    'code' => 'MOMO-' . strtoupper(substr($name, 0, 3)) . '-' . rand(1000, 9999),
                    'description' => $descriptions[$index],
                    'price' => rand(5, 20) + (rand(0, 99) / 100),
                    'stock' => rand(50, 200),
                    'image' => 'default.jpg',
                    'is_featured' => rand(0, 100) < 20,
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

        // Generate orders for the last 60 days
        $startDate = Carbon::now()->subDays(60);
        $endDate = Carbon::now();

        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
            // Generate 1-5 orders per day
            $ordersPerDay = rand(1, 5);
            
            for ($i = 0; $i < $ordersPerDay; $i++) {
                $order = Order::create([
                    'user_id' => $users[array_rand($users)]->id,
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'order_type' => 'dine_in',
                    'status' => ['completed', 'completed', 'completed', 'cancelled'][array_rand(['completed', 'completed', 'completed', 'cancelled'])],
                    'payment_status' => 'paid',
                    'payment_method' => ['credit_card', 'cash', 'online'][array_rand(['credit_card', 'cash', 'online'])],
                    'shipping_address' => json_encode(['address' => '123 Main St, City, Country']),
                    'subtotal' => 0,
                    'tax' => 0,
                    'discount' => 0,
                    'total' => 0,
                    'created_at' => $date->copy()->addHours(rand(9, 21)),
                ]);

                // Add 1-4 items to each order
                $totalAmount = 0;
                $orderItems = rand(1, 4);
                
                for ($j = 0; $j < $orderItems; $j++) {
                    $product = $products[array_rand($products)];
                    $quantity = rand(1, 5);
                    $price = $product->price;
                    $subtotal = $price * $quantity;
                    $totalAmount += $subtotal;
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'item_name' => $product->name,
                        'subtotal' => $subtotal,
                    ]);
                }

                // Update order totals
                $tax = $totalAmount * 0.13; // 13% tax
                $order->update([
                    'subtotal' => $totalAmount,
                    'tax' => $tax,
                    'total' => $totalAmount + $tax
                ]);
            }
        }
    }
} 