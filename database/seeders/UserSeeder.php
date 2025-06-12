<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'phone' => '09123456789',
            ]
        );
        $admin->assignRole('admin');

        // Create manager user
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'phone' => '09234567890',
            ]
        );
        $manager->assignRole('employee.manager');

        // Create cashier user
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@example.com'],
            [
                'name' => 'Cashier User',
                'password' => Hash::make('password'),
                'phone' => '09345678901',
            ]
        );
        $cashier->assignRole('employee.cashier');

        // Create regular employee user
        $employee = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Regular Employee',
                'password' => Hash::make('password'),
                'phone' => '09456789012',
            ]
        );
        $employee->assignRole('employee.regular');

        // Create some regular users
        for ($i = 1; $i <= 5; $i++) {
            $user = User::firstOrCreate(
                ['email' => "user$i@example.com"],
                [
                    'name' => "User $i",
                    'password' => Hash::make('password'),
                    'phone' => "09" . str_pad($i, 9, '0', STR_PAD_LEFT),
                ]
            );
            $user->assignRole('user');
        }

        $this->command->info('Dummy users created successfully!');
    }
} 