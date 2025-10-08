<?php

/**
 * Delete Test Users (Keep Real Users)
 * Preserves: sabstha98@gmail.com and any other non-@example.com users
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "========================================\n";
echo "DELETE TEST USERS\n";
echo "========================================\n\n";

// List all current users
echo "Current users:\n";
$allUsers = User::all(['id', 'name', 'email', 'role']);
foreach ($allUsers as $user) {
    $marker = str_contains($user->email, '@example.com') ? '❌ DELETE' : '✅ KEEP';
    echo "  [$marker] ID:{$user->id} - {$user->name} ({$user->email}) - Role: {$user->role}\n";
}

echo "\n";

// Count test users
$testUserCount = User::where('email', 'like', '%@example.com')->count();
echo "Test users to delete: $testUserCount\n";

$realUserCount = User::where('email', 'not like', '%@example.com')->count();
echo "Real users to keep: $realUserCount\n\n";

// Delete role assignments first
echo "Deleting role/permission assignments for test users...\n";
$testUserIds = User::where('email', 'like', '%@example.com')->pluck('id');

if ($testUserIds->isNotEmpty()) {
    // Delete from model_has_roles
    DB::table('model_has_roles')
        ->where('model_type', 'App\\Models\\User')
        ->whereIn('model_id', $testUserIds)
        ->delete();
    
    // Delete from model_has_permissions (if exists)
    if (Schema::hasTable('model_has_permissions')) {
        DB::table('model_has_permissions')
            ->where('model_type', 'App\\Models\\User')
            ->whereIn('model_id', $testUserIds)
            ->delete();
    }
    
    echo "✅ Role assignments deleted\n\n";
}

// Delete test users
echo "Deleting test users with @example.com emails...\n";
$deleted = User::where('email', 'like', '%@example.com')->delete();
echo "✅ Deleted: $deleted test users\n\n";

// Verify
echo "========================================\n";
echo "VERIFICATION\n";
echo "========================================\n\n";

$remaining = User::all(['id', 'name', 'email', 'role']);
echo "Remaining users ($remaining->count()):\n";
foreach ($remaining as $user) {
    echo "  ✅ ID:{$user->id} - {$user->name} ({$user->email}) - Role: {$user->role}\n";
}

echo "\n";

// Verify sabstha98@gmail.com still exists
$realUser = User::where('email', 'sabstha98@gmail.com')->first();
if ($realUser) {
    echo "✅ VERIFIED: sabstha98@gmail.com still exists (ID: {$realUser->id})\n";
} else {
    echo "❌ WARNING: sabstha98@gmail.com not found!\n";
}

echo "\n✅ Test user deletion complete!\n\n";

