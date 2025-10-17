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

        // Then seed branches (required for other seeders)
        $this->call(BranchSeeder::class);

        // Then seed the rest of the data
        $this->call([
            // ProductSeeder::class, // REMOVED - Using MenuSeeder instead (has real menu data)
            PaymentMethodSeeder::class,
        ]);

        // Other essential seeders
        $this->call(TaxDeliverySettingsSeeder::class);
        $this->call(ExpenseSeeder::class);
        $this->call(MenuSeeder::class);
        
        // Note: Branch updates and impact stats are now auto-generated from real data
    }
}
