<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            SalesDataSeeder::class,
            TableSeeder::class,
            StockItemSeeder::class,
            CouponSeeder::class,
            RoleSeeder::class,
            // Add other seeders here
        ]);

        // Other seeders
        // $this->call(CreatorsTableSeeder::class); // Removed because file does not exist
        $this->call(SupplierSeeder::class);
    }
}
