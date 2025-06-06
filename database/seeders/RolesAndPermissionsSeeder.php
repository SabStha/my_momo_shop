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
        $creatorRole = Role::firstOrCreate(['name' => 'creator', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

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
            
            // User permissions
            'view products',
            'place orders',
            'view own orders',
            'manage own profile',
            'view own wallet'
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
        
        $userRole->syncPermissions([
            'view products',
            'place orders',
            'view own orders',
            'manage own profile',
            'view own wallet'
        ]);

        $this->command->info('Roles and permissions created successfully!');
    }
} 