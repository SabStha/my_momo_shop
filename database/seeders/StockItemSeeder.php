<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockItem;

class StockItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Rice',
                'category' => 'Grains',
                'quantity' => 100,
                'unit' => 'kg',
                'cost' => 50.00,
                'expiry' => now()->addMonths(6),
            ],
            [
                'name' => 'Sugar',
                'category' => 'Baking',
                'quantity' => 50,
                'unit' => 'kg',
                'cost' => 30.00,
                'expiry' => now()->addMonths(12),
            ],
            [
                'name' => 'Salt',
                'category' => 'Seasoning',
                'quantity' => 20,
                'unit' => 'kg',
                'cost' => 10.00,
                'expiry' => now()->addMonths(24),
            ],
            [
                'name' => 'Flour',
                'category' => 'Baking',
                'quantity' => 75,
                'unit' => 'kg',
                'cost' => 40.00,
                'expiry' => now()->addMonths(3),
            ],
            [
                'name' => 'Oil',
                'category' => 'Cooking',
                'quantity' => 30,
                'unit' => 'L',
                'cost' => 25.00,
                'expiry' => now()->addMonths(9),
            ],
        ];

        foreach ($items as $item) {
            StockItem::create($item);
        }
    }
} 