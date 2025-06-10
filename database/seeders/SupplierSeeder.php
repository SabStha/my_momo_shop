<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        Supplier::firstOrCreate(
            ['code' => 'SUP001'],
            [
                'name' => 'Default Supplier',
                'contact_person' => 'John Doe',
                'email' => 'default@supplier.com',
                'phone' => '1234567890',
                'address' => '123 Supplier Street, City',
                'notes' => 'Default supplier for testing',
                'is_active' => true
            ]
        );
    }
} 