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
        // First seed roles and permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // Then seed users with their roles
        $this->call(UserSeeder::class);

        // Then seed the rest of the data
        $this->call([
            ProductSeeder::class,
            SupplierSeeder::class,
            TableSeeder::class,
            StockItemSeeder::class,
            CouponSeeder::class,
            OrderSeeder::class,
            SalesDataSeeder::class,
            RoleSeeder::class,
        ]);
    }
}
