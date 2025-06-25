<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Create investor role if it doesn't exist
        $investorRole = Role::firstOrCreate(['name' => 'investor']);

        // Create necessary permissions
        $permissions = [
            'view_customer_analytics',
            'view_sales_analytics',
            'manage_products',
            'manage_orders',
            'manage_settings',
            'view_investor_dashboard',
            'view_investor_reports',
            'view_investor_payouts',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to admin role
        $adminRole->givePermissionTo($permissions);

        // Assign investor-specific permissions to investor role
        $investorPermissions = [
            'view_investor_dashboard',
            'view_investor_reports',
            'view_investor_payouts',
        ];
        $investorRole->givePermissionTo($investorPermissions);
    }
} 