<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        // Get or create main branch
        $mainBranch = Branch::where('is_main', true)->first();
        
        if (!$mainBranch) {
            $mainBranch = Branch::create([
                'name' => 'Main Branch',
                'code' => 'MB001',
                'address' => 'Main Branch Address',
                'contact_person' => 'Main Branch Contact',
                'email' => 'main@momoshop.com',
                'phone' => '1234567890',
                'is_active' => true,
                'is_main' => true
            ]);
        }

        Supplier::firstOrCreate(
            ['code' => 'SUP001'],
            [
                'name' => 'Default Supplier',
                'contact_person' => 'John Doe',
                'email' => 'default@supplier.com',
                'phone' => '1234567890',
                'address' => '123 Supplier Street, City',
                'notes' => 'Default supplier for testing',
                'is_active' => true,
                'branch_id' => $mainBranch->id
            ]
        );
    }
} 