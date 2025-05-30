<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Table;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        $tables = [
            ['name' => 'Table 1', 'status' => 'available'],
            ['name' => 'Table 2', 'status' => 'available'],
            ['name' => 'Table 3', 'status' => 'available'],
            ['name' => 'Table 4', 'status' => 'available'],
            ['name' => 'Table 5', 'status' => 'available'],
        ];
        foreach ($tables as $table) {
            Table::create($table);
        }
    }
} 