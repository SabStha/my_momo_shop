<?php
/**
 * List All Users
 * 
 * Shows all users in the database with their details
 * Run: php list_all_users.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "\n";
echo "========================================\n";
echo "All Users in Database\n";
echo "========================================\n\n";

$users = User::orderBy('created_at', 'desc')->get();

if ($users->isEmpty()) {
    echo "❌ No users found in database.\n\n";
    exit(0);
}

echo "Total Users: " . $users->count() . "\n\n";

foreach ($users as $user) {
    echo "----------------------------------------\n";
    echo "ID: " . $user->id . "\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Phone: " . ($user->phone ?? 'N/A') . "\n";
    echo "Role: " . ($user->role ?? 'customer') . "\n";
    
    // Get roles from relationship if available
    if (method_exists($user, 'roles')) {
        $roles = $user->roles->pluck('name')->implode(', ');
        if ($roles) {
            echo "Roles: " . $roles . "\n";
        }
    }
    
    echo "Created: " . $user->created_at->format('Y-m-d H:i:s') . "\n";
    echo "Last Updated: " . $user->updated_at->format('Y-m-d H:i:s') . "\n";
    echo "Email Verified: " . ($user->email_verified_at ? 'Yes ✅' : 'No ❌') . "\n";
}

echo "----------------------------------------\n\n";
echo "To reset a user's password, run:\n";
echo "  php quick_password_reset.php <email> [password]\n\n";
echo "Example:\n";
echo "  php quick_password_reset.php sabstha98@gmail.com password123\n\n";

