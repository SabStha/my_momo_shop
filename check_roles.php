<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

echo "=== Checking Roles and Users ===\n\n";

// Check available roles
echo "Available roles:\n";
$roles = Role::all();
foreach ($roles as $role) {
    echo "- {$role->name}\n";
}

echo "\n";

// Check latest user
$user = User::latest()->first();
if ($user) {
    echo "Latest user: {$user->name} (ID: {$user->id})\n";
    echo "User roles: " . ($user->roles->pluck('name')->implode(', ') ?: 'none') . "\n";
} else {
    echo "No users found in database.\n";
}

// Check user 34 specifically
echo "\n";
$user34 = User::find(34);
if ($user34) {
    echo "User 34: {$user34->name} (ID: {$user34->id})\n";
    echo "Email: {$user34->email}\n";
    echo "Phone: {$user34->phone}\n";
    echo "User roles: " . ($user34->roles->pluck('name')->implode(', ') ?: 'none') . "\n";
} else {
    echo "User 34 not found in database.\n";
}

echo "\n=== End ===\n"; 