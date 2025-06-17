<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing payment methods
        PaymentMethod::truncate();

        // Create payment methods
        $methods = [
            [
                'name' => 'Credit Card',
                'code' => 'credit_card',
                'description' => 'Pay with credit or debit card',
                'is_active' => true,
                'requires_online_payment' => true,
                'icon' => 'credit-card',
                'sort_order' => 1
            ],
            [
                'name' => 'Wallet',
                'code' => 'wallet',
                'description' => 'Pay using your wallet balance',
                'is_active' => true,
                'requires_online_payment' => true,
                'icon' => 'wallet',
                'sort_order' => 2
            ],
            [
                'name' => 'Khalti',
                'code' => 'khalti',
                'description' => 'Pay using Khalti payment gateway',
                'is_active' => true,
                'requires_online_payment' => true,
                'icon' => 'khalti',
                'sort_order' => 3
            ],
            [
                'name' => 'Cash',
                'code' => 'cash',
                'description' => 'Pay with cash at the counter',
                'is_active' => true,
                'requires_online_payment' => false,
                'icon' => 'money-bill',
                'sort_order' => 4
            ]
        ];

        foreach ($methods as $method) {
            PaymentMethod::create($method);
        }
    }
} 