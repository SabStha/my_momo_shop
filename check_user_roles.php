<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking user roles...\n\n";

$user = \App\Models\User::find(31);
if ($user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    echo "Email: {$user->email}\n";
    
    $roles = $user->roles;
    echo "Current roles: ";
    if ($roles->count() > 0) {
        echo $roles->pluck('name')->join(', ') . "\n";
    } else {
        echo "No roles assigned\n";
    }
    
    // Check if user has POS access roles
    $hasPosAccess = $user->hasAnyRole(['admin', 'employee.manager', 'employee.cashier']);
    echo "Has POS access: " . ($hasPosAccess ? 'Yes' : 'No') . "\n";
    
    if (!$hasPosAccess) {
        echo "\nAssigning admin role...\n";
        $user->assignRole('admin');
        echo "✓ Admin role assigned\n";
        
        // Verify
        $hasPosAccess = $user->hasAnyRole(['admin', 'employee.manager', 'employee.cashier']);
        echo "Has POS access now: " . ($hasPosAccess ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "User not found\n";
}

echo "\nDone!\n";


