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

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $investorRole = Role::firstOrCreate(['name' => 'investor', 'guard_name' => 'web']);
        $creatorRole = Role::firstOrCreate(['name' => 'creator', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'employee.manager', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'employee.cashier', 'guard_name' => 'web']);
        $regularEmployeeRole = Role::firstOrCreate(['name' => 'employee.regular', 'guard_name' => 'web']);

        // Create permissions
        $permissions = [
            // Admin permissions
            'view admin dashboard',
            'manage users',
            'manage roles',
            'manage permissions',
            'manage products',
            'manage orders',
            'manage categories',
            'manage settings',
            'manage wallet',
            'manage creators',
            
            // Creator permissions
            'view creator dashboard',
            'manage own profile',
            'view own referrals',
            'view own earnings',
            
            // Investor permissions
            'view investor dashboard',
            'view own investments',
            'view own payouts',
            'view investor reports',
            
            // User permissions
            'view products',
            'place orders',
            'view own orders',
            'manage own profile',
            'view own wallet',

            // Employee permissions
            'view employee dashboard',
            'manage inventory',
            'manage orders',
            'manage tables',
            'view reports',
            'manage employees',
            'process payments',
            'view sales',
            'manage menu',
            'manage reservations'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        $adminRole->syncPermissions(Permission::all());
        
        $creatorRole->syncPermissions([
            'view creator dashboard',
            'manage own profile',
            'view own referrals',
            'view own earnings'
        ]);
        
        $investorRole->syncPermissions([
            'view investor dashboard',
            'view own investments',
            'view own payouts',
            'view investor reports',
            'manage own profile'
        ]);
        
        $userRole->syncPermissions([
            'view products',
            'place orders',
            'view own orders',
            'manage own profile',
            'view own wallet'
        ]);

        // Manager permissions
        $managerRole->syncPermissions([
            'view employee dashboard',
            'manage inventory',
            'manage orders',
            'manage tables',
            'view reports',
            'manage employees',
            'process payments',
            'view sales',
            'manage menu',
            'manage reservations'
        ]);

        // Cashier permissions
        $cashierRole->syncPermissions([
            'view employee dashboard',
            'process payments',
            'view sales',
            'manage orders',
            'manage tables'
        ]);

        // Regular employee permissions
        $regularEmployeeRole->syncPermissions([
            'view employee dashboard',
            'manage orders',
            'manage tables',
            'view sales'
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
} 