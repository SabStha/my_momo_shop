<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BulkPackage;

class BulkPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            // Cooked Packages
            [
                'name' => 'Party Pack',
                'emoji' => '🎉',
                'description' => 'For events, 8–10 people',
                'type' => 'cooked',
                'package_key' => 'party',
                'items' => [
                    ['name' => '80 pcs mixed momos', 'price' => 1200],
                    ['name' => '4 sides', 'price' => 400],
                    ['name' => '2 sauces', 'price' => 100],
                    ['name' => 'Delivery', 'price' => 299]
                ],
                'total_price' => 1999,
                'sort_order' => 1
            ],
            [
                'name' => 'Family Feast',
                'emoji' => '👨‍👩‍👧‍👦',
                'description' => 'For 4–5 people at home',
                'type' => 'cooked',
                'package_key' => 'family',
                'items' => [
                    ['name' => '40 pcs (choose type)', 'price' => 600],
                    ['name' => '2 sides', 'price' => 200],
                    ['name' => '1 drink', 'price' => 50],
                    ['name' => 'Delivery', 'price' => 149]
                ],
                'total_price' => 999,
                'sort_order' => 2
            ],
            [
                'name' => 'Office Saver',
                'emoji' => '🧑‍💻',
                'description' => 'Lunch for a small office team',
                'type' => 'cooked',
                'package_key' => 'office',
                'items' => [
                    ['name' => '60 pcs mixed', 'price' => 900],
                    ['name' => '3 sides', 'price' => 300],
                    ['name' => 'Eco packaging', 'price' => 50],
                    ['name' => 'Delivery', 'price' => 249]
                ],
                'total_price' => 1499,
                'sort_order' => 3
            ],
            
            // Frozen Packages
            [
                'name' => 'Freezer Stock',
                'emoji' => '🧊',
                'description' => 'For home storage, 2-3 weeks',
                'type' => 'frozen',
                'package_key' => 'freezer',
                'items' => [
                    ['name' => '100 pcs mixed', 'price' => 1500],
                    ['name' => 'Vacuum packed', 'price' => 200],
                    ['name' => 'Storage guide', 'price' => 0],
                    ['name' => 'Pickup', 'price' => 0]
                ],
                'total_price' => 1700,
                'sort_order' => 1
            ],
            [
                'name' => 'Bulk Saver',
                'emoji' => '💰',
                'description' => 'Maximum value, 4-6 weeks',
                'type' => 'frozen',
                'package_key' => 'bulk',
                'items' => [
                    ['name' => '200 pcs mixed', 'price' => 2800],
                    ['name' => 'Vacuum packed', 'price' => 300],
                    ['name' => 'Bulk discount', 'price' => -400],
                    ['name' => 'Pickup', 'price' => 0]
                ],
                'total_price' => 2700,
                'sort_order' => 2
            ],
            [
                'name' => 'Gift Pack',
                'emoji' => '🎁',
                'description' => 'Perfect for gifting',
                'type' => 'frozen',
                'package_key' => 'gift',
                'items' => [
                    ['name' => '50 pcs premium', 'price' => 800],
                    ['name' => 'Gift packaging', 'price' => 150],
                    ['name' => 'Recipe card', 'price' => 50],
                    ['name' => 'Delivery', 'price' => 200]
                ],
                'total_price' => 1200,
                'sort_order' => 3
            ]
        ];

        foreach ($packages as $package) {
            // Check if package already exists by package_key
            $existingPackage = BulkPackage::where('package_key', $package['package_key'])->first();
            
            if (!$existingPackage) {
                BulkPackage::create($package);
                $this->command->info("Created bulk package: {$package['name']}");
            } else {
                $this->command->info("Bulk package already exists: {$package['name']} (skipping)");
            }
        }
    }
}
