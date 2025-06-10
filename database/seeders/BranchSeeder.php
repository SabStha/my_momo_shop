<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    public function run()
    {
        // Create main branch
        Branch::firstOrCreate(
            ['code' => 'MB001'],
            [
                'name' => 'Main Branch',
                'address' => '123 Main Street, City Center',
                'contact_person' => 'John Doe',
                'email' => 'main@momoshop.com',
                'phone' => '1234567890',
                'is_active' => true,
                'is_main' => true
            ]
        );

        // Create additional branches
        Branch::firstOrCreate(
            ['code' => 'NB001'],
            [
                'name' => 'North Branch',
                'address' => '456 North Avenue, North District',
                'contact_person' => 'Jane Smith',
                'email' => 'north@momoshop.com',
                'phone' => '2345678901',
                'is_active' => true,
                'is_main' => false
            ]
        );

        Branch::firstOrCreate(
            ['code' => 'SB001'],
            [
                'name' => 'South Branch',
                'address' => '789 South Road, South District',
                'contact_person' => 'Mike Johnson',
                'email' => 'south@momoshop.com',
                'phone' => '3456789012',
                'is_active' => true,
                'is_main' => false
            ]
        );
    }
} 