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

        // Then seed branches (required for other seeders)
        $this->call(BranchSeeder::class);

        // Then seed the rest of the data
        $this->call([
            ProductSeeder::class,
            SupplierSeeder::class,
            TableSeeder::class,
            StockItemSeeder::class,
            MerchandiseSeeder::class,
            BulkPackageSeeder::class,
            CouponSeeder::class,
            OfferSeeder::class,
            SalesDataSeeder::class, // This includes orders, so we don't need OrderSeeder
            PaymentMethodSeeder::class,
            StatisticsSeeder::class, // Add sample data for statistics
        ]);

        // Other seeders
        $this->call(CustomerSegmentSeeder::class);
    }
}
