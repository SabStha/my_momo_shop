<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample location data for Kathmandu area
        $locations = [
            ['lat' => 27.7172, 'lng' => 85.3240, 'delivery_fee' => 5], // Thamel
            ['lat' => 27.7120, 'lng' => 85.3270, 'delivery_fee' => 6], // Durbar Marg
            ['lat' => 27.7220, 'lng' => 85.3180, 'delivery_fee' => 7], // Baneshwor
            ['lat' => 27.7080, 'lng' => 85.3300, 'delivery_fee' => 8], // Patan
            ['lat' => 27.7320, 'lng' => 85.3100, 'delivery_fee' => 9], // Koteshwor
        ];

        // Get non-main active branches
        $branches = Branch::where('is_main', false)
                         ->where('is_active', true)
                         ->take(count($locations))
                         ->get();

        foreach ($branches as $index => $branch) {
            if (isset($locations[$index])) {
                $location = $locations[$index];
                $branch->update([
                    'latitude' => $location['lat'],
                    'longitude' => $location['lng'],
                    'delivery_fee' => $location['delivery_fee'],
                    'delivery_radius_km' => 5
                ]);
                
                $this->command->info("Updated {$branch->name} with location: {$location['lat']}, {$location['lng']}");
            }
        }
    }
}
