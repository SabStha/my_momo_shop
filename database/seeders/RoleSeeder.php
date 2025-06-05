<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);
        $cashierRole = Role::firstOrCreate(['name' => 'cashier']);

        // Create permissions
        $permissions = [
            'manage_orders',
            'manage_products',
            'manage_inventory',
            'manage_employees',
            'manage_users',
            'manage_settings',
            'view_reports',
            'manage_payments',
            'access_pos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to admin role
        $adminRole->givePermissionTo($permissions);

        // Assign specific permissions to employee role
        $employeeRole->givePermissionTo([
            'manage_orders',
            'manage_products',
            'access_pos',
        ]);

        // Assign specific permissions to cashier role
        $cashierRole->givePermissionTo([
            'manage_orders',
            'access_pos',
            'manage_payments',
        ]);
    }
} 