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
            MenuDataSeeder::class, // Add this to properly categorize products
            SupplierSeeder::class,
            TableSeeder::class,
            StockItemSeeder::class,
            MerchandiseSeeder::class,
            BulkPackageSeeder::class,
            CouponSeeder::class,
            OfferSeeder::class,
            // SalesDataSeeder::class, // REMOVED - test data seeder
            PaymentMethodSeeder::class,
            // StatisticsSeeder::class, // REMOVED - test data seeder
        ]);

        // Other seeders
        $this->call(CustomerSegmentSeeder::class);
        $this->call(TaxDeliverySettingsSeeder::class);
    }
}
