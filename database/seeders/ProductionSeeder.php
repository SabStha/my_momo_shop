<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Production-safe seeder - only creates essential data
     * DO NOT run development seeders on production
     */
    public function run(): void
    {
        $this->command->info('🌱 Running Production Seeder...');
        
        // Only seed roles and permissions (no dummy data)
        $this->call(RolesAndPermissionsSeeder::class);
        
        // Create a single admin user (you should change this)
        $this->createAdminUser();
        
        // Seed essential settings only
        $this->call([
            SiteSettingsSeeder::class,
            PaymentMethodSeeder::class,
            TaxDeliverySettingsSeeder::class,
        ]);
        
        $this->command->info('✅ Production seeding completed successfully!');
        $this->command->warn('⚠️  Remember to change the default admin password!');
    }
    
    /**
     * Create a single admin user for initial access
     */
    private function createAdminUser(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@yourdomain.com'], // CHANGE THIS EMAIL
            [
                'name' => 'System Administrator',
                'password' => Hash::make('ChangeThisPassword123!'), // CHANGE THIS PASSWORD
                'phone' => '1234567890', // CHANGE THIS PHONE
                'email_verified_at' => now(),
            ]
        );
        
        if ($admin->wasRecentlyCreated) {
            $admin->assignRole('admin');
            $this->command->info('👤 Admin user created: admin@yourdomain.com');
        } else {
            $this->command->info('👤 Admin user already exists');
        }
    }
}
