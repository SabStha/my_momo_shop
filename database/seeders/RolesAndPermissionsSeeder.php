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
            'view products',
            'create products',
            'edit products',
            'delete products',
            'view orders',
            'manage orders',
            'view users',
            'manage users',
            'manage payments',
            'access pos',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $roles = [
            'admin' => $permissions,
            'cashier' => [
                'view products',
                'view orders',
                'manage orders',
                'manage payments',
                'access pos',
            ],
            'employee' => [
                'view products',
                'edit products',
                'view orders',
                'manage orders',
            ],
            'user' => [
                'view products',
            ],
        ];

        foreach ($roles as $role => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $role]);
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('Roles and permissions created successfully!');
    }
} 