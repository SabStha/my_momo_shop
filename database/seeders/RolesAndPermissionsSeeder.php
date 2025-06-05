<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User permissions
            'view profile',
            'edit profile',
            'place orders',
            'view orders',
            'view products',
            
            // Regular Employee permissions
            'clock in',
            'clock out',
            'view own orders',
            'create orders',
            
            // Cashier permissions
            'access pos',
            'process payments',
            'manage payments',
            'view reports',
            'edit orders',
            
            // Manager permissions
            'manage inventory',
            'manage employees',
            'view all orders',
            'manage schedules',
            'generate reports',
            'manage products',
            
            // Admin permissions
            'manage users',
            'manage roles',
            'manage permissions',
            'manage settings',
            'manage analytics',
            'view all reports'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Define role permissions
        $rolePermissions = [
            'user' => [
                'view profile',
                'edit profile',
                'place orders',
                'view orders',
                'view products'
            ],
            
            'employee.regular' => [
                'view profile',
                'edit profile',
                'place orders',
                'view orders',
                'view products',
                'clock in',
                'clock out',
                'view own orders',
                'create orders'
            ],
            
            'employee.cashier' => [
                'view profile',
                'edit profile',
                'place orders',
                'view orders',
                'view products',
                'clock in',
                'clock out',
                'view own orders',
                'create orders',
                'access pos',
                'process payments',
                'manage payments',
                'view reports',
                'edit orders'
            ],
            
            'employee.manager' => [
                'view profile',
                'edit profile',
                'place orders',
                'view orders',
                'view products',
                'clock in',
                'clock out',
                'view own orders',
                'create orders',
                'access pos',
                'process payments',
                'manage payments',
                'view reports',
                'edit orders',
                'manage inventory',
                'manage employees',
                'view all orders',
                'manage schedules',
                'generate reports',
                'manage products'
            ],
            
            'admin' => $permissions // Admin has all permissions
        ];

        // Create roles and assign permissions
        foreach ($rolePermissions as $role => $permissions) {
            $role = Role::firstOrCreate(['name' => $role]);
            $role->syncPermissions($permissions);
        }

        $this->command->info('Roles and permissions created successfully!');
    }
} 