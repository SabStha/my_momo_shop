<?php
/**
 * Check and Reset User Password
 * 
 * This script checks if a user exists and optionally resets their password
 * Run: php check_and_reset_user.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// User email to check
$email = 'sabstha98@gmail.com';

echo "\n";
echo "========================================\n";
echo "User Account Check & Password Reset\n";
echo "========================================\n\n";

// Check if user exists
$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found: $email\n\n";
    echo "Would you like to create this user? (This needs to be done through registration)\n";
    exit(1);
}

echo "✅ User found!\n\n";
echo "User Details:\n";
echo "  ID: " . $user->id . "\n";
echo "  Name: " . $user->name . "\n";
echo "  Email: " . $user->email . "\n";
echo "  Phone: " . ($user->phone ?? 'N/A') . "\n";
echo "  Role: " . ($user->role ?? 'customer') . "\n";
echo "  Created: " . $user->created_at . "\n";
echo "  Email Verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n";
echo "\n";

// Ask if user wants to reset password
echo "Do you want to reset the password? (yes/no): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$response = trim(strtolower($line));
fclose($handle);

if ($response !== 'yes' && $response !== 'y') {
    echo "\n❌ Password reset cancelled.\n\n";
    exit(0);
}

// Ask for new password
echo "\nEnter new password (or press Enter for 'password123'): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
$newPassword = trim($line);
fclose($handle);

if (empty($newPassword)) {
    $newPassword = 'password123';
    echo "Using default password: password123\n";
}

// Reset password
try {
    $user->password = Hash::make($newPassword);
    $user->save();
    
    echo "\n✅ Password reset successful!\n\n";
    echo "Login Credentials:\n";
    echo "  Email: " . $user->email . "\n";
    echo "  Password: " . $newPassword . "\n";
    echo "\n";
    echo "You can now login with these credentials!\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error resetting password: " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "========================================\n\n";

