<?php

namespace Database\Seeders;

use App\Models\CashDenomination;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CashDenominationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $denominations = [
            ['name' => '1000', 'value' => 1000.00, 'quantity' => 0],
            ['name' => '500', 'value' => 500.00, 'quantity' => 0],
            ['name' => '200', 'value' => 200.00, 'quantity' => 0],
            ['name' => '100', 'value' => 100.00, 'quantity' => 0],
            ['name' => '50', 'value' => 50.00, 'quantity' => 0],
            ['name' => '20', 'value' => 20.00, 'quantity' => 0],
            ['name' => '10', 'value' => 10.00, 'quantity' => 0],
            ['name' => '5', 'value' => 5.00, 'quantity' => 0],
            ['name' => '1', 'value' => 1.00, 'quantity' => 0],
            ['name' => '0.5', 'value' => 0.50, 'quantity' => 0],
            ['name' => '0.25', 'value' => 0.25, 'quantity' => 0],
        ];

        foreach ($denominations as $denomination) {
            CashDenomination::create($denomination);
        }
    }
}
