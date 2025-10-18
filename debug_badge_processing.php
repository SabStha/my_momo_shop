<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\BadgeProgressionService;
use App\Models\User;
use App\Models\BadgeClass;
use App\Models\Order;

echo "🐛 Debug Badge Processing for User ID 1...\n\n";

$user = User::find(1);
if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "👤 User: {$user->name}\n";
echo "📧 Email: {$user->email}\n\n";

// Check orders
$orders = $user->orders()->whereIn('status', ['completed', 'delivered', 'pending'])->get();
echo "📦 Eligible Orders: {$orders->count()}\n";
foreach ($orders->take(3) as $order) {
    echo "  • Order #{$order->id} - Status: {$order->status} - Total: Rs.{$order->total_amount}\n";
}
echo "\n";

// Check badge classes
$badgeClasses = BadgeClass::all();
echo "🏆 Badge Classes: {$badgeClasses->count()}\n";
foreach ($badgeClasses as $class) {
    echo "  • {$class->name} ({$class->code}) - ID: {$class->id}\n";
}
echo "\n";

// Check if user has AmaCredit
if (!$user->amaCredit) {
    echo "⚠️ No AmaCredit found - creating one...\n";
    try {
        \App\Models\AmaCredit::create([
            'user_id' => $user->id,
            'current_balance' => 0,
            'total_earned' => 0,
            'total_spent' => 0,
            'weekly_earned' => 0,
            'weekly_reset_date' => now()->startOfWeek()->addWeek()->toDateString(),
            'weekly_cap' => 1000,
            'last_activity_at' => now(),
        ]);
        echo "✅ AmaCredit created!\n\n";
    } catch (\Exception $e) {
        echo "❌ Failed to create AmaCredit: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "✅ AmaCredit exists - Balance: Rs.{$user->amaCredit->current_balance}\n\n";
}

// Now try to run badge progression with full error output
echo "🔄 Running Badge Progression Service...\n\n";

try {
    $badgeService = new BadgeProgressionService();
    
    echo "  Step 1: Processing loyalty progression...\n";
    // We'll call the methods directly to see where it fails
    $reflection = new ReflectionClass($badgeService);
    
    // Get the processLoyaltyProgression method
    $loyaltyMethod = $reflection->getMethod('processLoyaltyProgression');
    $loyaltyMethod->setAccessible(true);
    
    try {
        $loyaltyMethod->invoke($badgeService, $user);
        echo "  ✅ Loyalty progression completed\n";
    } catch (\Exception $e) {
        echo "  ❌ Loyalty progression failed:\n";
        echo "     Error: " . $e->getMessage() . "\n";
        echo "     File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "     Trace: " . substr($e->getTraceAsString(), 0, 500) . "...\n\n";
    }
    
    echo "  Step 2: Processing engagement progression...\n";
    $engagementMethod = $reflection->getMethod('processEngagementProgression');
    $engagementMethod->setAccessible(true);
    
    try {
        $engagementMethod->invoke($badgeService, $user);
        echo "  ✅ Engagement progression completed\n";
    } catch (\Exception $e) {
        echo "  ❌ Engagement progression failed:\n";
        echo "     Error: " . $e->getMessage() . "\n";
        echo "     File: " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "     Trace: " . substr($e->getTraceAsString(), 0, 500) . "...\n\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Badge Service Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack:\n" . $e->getTraceAsString() . "\n\n";
}

// Check results
echo "\n📊 Results After Processing:\n";
$userBadges = DB::table('user_badges')->where('user_id', 1)->count();
$badgeProgress = DB::table('badge_progress')->where('user_id', 1)->get();

echo "  User Badges: {$userBadges}\n";
echo "  Badge Progress Records: {$badgeProgress->count()}\n";

if ($badgeProgress->count() > 0) {
    foreach ($badgeProgress as $progress) {
        $class = BadgeClass::find($progress->badge_class_id);
        echo "  • {$class->name}: {$progress->current_points} points\n";
    }
}

echo "\n✅ Debug complete!\n";

