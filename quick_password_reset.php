<?php
/**
 * Quick Password Reset - No prompts, just reset to 'password123'
 * 
 * Run: php quick_password_reset.php sabstha98@gmail.com
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Get email from command line argument or use default
$email = $argv[1] ?? 'sabstha98@gmail.com';
$newPassword = $argv[2] ?? 'password123';

echo "\n========================================\n";
echo "Quick Password Reset\n";
echo "========================================\n\n";

// Find user
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found: $email\n\n";
    
    // List all users
    echo "Available users:\n";
    $users = User::all();
    foreach ($users as $u) {
        echo "  - " . $u->email . " (ID: " . $u->id . ", Name: " . $u->name . ")\n";
    }
    echo "\n";
    exit(1);
}

// Reset password
try {
    $user->password = Hash::make($newPassword);
    $user->save();
    
    echo "✅ Password reset successful!\n\n";
    echo "Login Credentials:\n";
    echo "  Email: " . $user->email . "\n";
    echo "  Password: " . $newPassword . "\n";
    echo "\n";
    echo "🎉 You can now login with these credentials!\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
    exit(1);
}

