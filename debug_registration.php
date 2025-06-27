<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== Debug Registration Flow ===\n\n";

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
    
    // Test isAdmin method
    echo "Is admin: " . ($user->isAdmin() ? 'YES' : 'NO') . "\n";
    echo "Has admin role: " . ($user->hasRole('admin') ? 'YES' : 'NO') . "\n";
    echo "Has user role: " . ($user->hasRole('user') ? 'YES' : 'NO') . "\n";
    echo "Has customer role: " . ($user->hasRole('customer') ? 'YES' : 'NO') . "\n";
    
    // Test redirect logic
    echo "\nRedirect logic test:\n";
    if ($user->hasRole('admin')) {
        echo "Would redirect to: admin.branches.index\n";
    } elseif ($user->hasRole('creator')) {
        echo "Would redirect to: creator.dashboard\n";
    } elseif ($user->hasRole('employee')) {
        echo "Would redirect to: employee.dashboard\n";
    } elseif ($user->hasRole('user') || $user->hasRole('customer')) {
        echo "Would redirect to: dashboard\n";
    } else {
        echo "Would redirect to: dashboard (fallback)\n";
    }
} else {
    echo "No users found in database.\n";
}

echo "\n=== End ===\n"; 