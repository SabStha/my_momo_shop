<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Get some random users
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->info('No users found. Please run UserSeeder first.');
            return;
        }

        // Get some random products
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->info('No products found. Please run ProductSeeder first.');
            return;
        }

        // Create 20 dummy orders
        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $orderDate = Carbon::now()->subDays(rand(0, 30));
            
            $order = Order::create([
                'user_id' => $user->id,
                'total_amount' => 0, // Will be updated after adding items
                'status' => collect(['pending', 'processing', 'completed', 'cancelled'])->random(),
                'shipping_address' => '123 Dummy Street, City, Country',
                'billing_address' => '123 Dummy Street, City, Country',
                'payment_method' => collect(['credit_card', 'paypal', 'bank_transfer'])->random(),
                'payment_status' => collect(['pending', 'paid', 'failed'])->random(),
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Add 1-3 random products to each order (reduced from 1-5)
            $totalAmount = 0;
            $orderProducts = $products->random(rand(1, 3));
            
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
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

            // Update order total amount
            $order->update(['total_amount' => $totalAmount]);
        }

        $this->command->info('Dummy orders created successfully!');
    }
} 