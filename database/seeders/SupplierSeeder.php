<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        DB::table('suppliers')->insert([
            'name' => 'Default Supplier',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
} 