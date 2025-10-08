<?php

/**
 * Check and Create User Role
 * 
 * This script checks if the 'user' role exists and creates it if it doesn't.
 * Run this from the project root: php check_and_create_user_role.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

echo "🔍 Checking for 'user' role...\n";

try {
    // Check if role exists
    $role = Role::where('name', 'user')->first();
    
    if ($role) {
        echo "✅ 'user' role already exists (ID: {$role->id})\n";
    } else {
        echo "❌ 'user' role does not exist. Creating it now...\n";
        
        // Create the role
        $role = Role::create(['name' => 'user', 'guard_name' => 'web']);
        
        echo "✅ 'user' role created successfully (ID: {$role->id})\n";
    }
    
    // List all existing roles
    echo "\n📋 All existing roles:\n";
    $allRoles = Role::all();
    foreach ($allRoles as $r) {
        echo "  - {$r->name} (ID: {$r->id}, Guard: {$r->guard_name})\n";
    }
    
    // Check for users without roles
    echo "\n👥 Checking users without roles...\n";
    $usersWithoutRoles = \App\Models\User::doesntHave('roles')->get();
    
    if ($usersWithoutRoles->count() > 0) {
        echo "Found {$usersWithoutRoles->count()} users without roles:\n";
        foreach ($usersWithoutRoles as $user) {
            echo "  - {$user->name} (ID: {$user->id}, Email: {$user->email})\n";
            
            // Ask if we should assign the role
            echo "    Assigning 'user' role...";
            $user->assignRole('user');
            echo " ✅ Done\n";
        }
    } else {
        echo "✅ All users have roles assigned\n";
    }
    
    echo "\n✅ All checks complete!\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

