<?php

/**
 * Test Automatic Badge Awarding
 * 
 * This script verifies that badges are awarded automatically when orders are placed
 * 
 * Usage: php test_automatic_badges.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Order;
use App\Models\BadgeClass;
use App\Events\OrderPlaced;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;

echo "🧪 Testing Automatic Badge Awarding System\n";
echo str_repeat("=", 60) . "\n\n";

// Step 1: Check Badge System Setup
echo "📋 Step 1: Checking Badge System Setup\n";
$badgeClassCount = BadgeClass::count();
echo "   ✅ Badge Classes in DB: {$badgeClassCount}\n";

if ($badgeClassCount === 0) {
    echo "   ❌ ERROR: No badge classes found! Run: php artisan db:seed --class=BadgeSystemSeeder\n";
    exit(1);
}

// Step 2: Get test user
echo "\n👤 Step 2: Getting Test User\n";
$user = User::first();

if (!$user) {
    echo "   ❌ ERROR: No users found in database!\n";
    exit(1);
}

echo "   ✅ Test User: {$user->name} (ID: {$user->id})\n";

// Check current badge count
$currentBadges = $user->badges()->count();
echo "   📊 Current Badges: {$currentBadges}\n";

// Step 3: Check Event Listener Registration
echo "\n🎧 Step 3: Checking Event Listeners\n";
$listeners = Event::getListeners('App\Events\OrderPlaced');
echo "   ✅ Listeners registered for OrderPlaced: " . count($listeners) . "\n";

foreach ($listeners as $listener) {
    echo "      • " . (is_string($listener) ? $listener : get_class($listener)) . "\n";
}

// Step 4: Check Latest Order
echo "\n📦 Step 4: Checking Latest Order\n";
$latestOrder = Order::where('user_id', $user->id)
    ->orWhere('created_by', $user->id)
    ->latest()
    ->first();

if ($latestOrder) {
    echo "   ✅ Latest Order: #{$latestOrder->id}\n";
    echo "      Status: {$latestOrder->status}\n";
    echo "      Created: {$latestOrder->created_at}\n";
    echo "      User ID: {$latestOrder->user_id}\n";
    echo "      Created By: {$latestOrder->created_by}\n";
} else {
    echo "   ⚠️  No orders found for this user\n";
}

// Step 5: Simulate OrderPlaced Event
echo "\n🔥 Step 5: Simulating OrderPlaced Event (if order exists)\n";

if ($latestOrder) {
    echo "   🎯 Firing OrderPlaced event...\n";
    
    try {
        event(new OrderPlaced($latestOrder));
        echo "   ✅ Event fired successfully!\n";
        
        // Wait a moment for event processing
        sleep(1);
        
        // Check if badges changed
        $newBadgeCount = $user->fresh()->badges()->count();
        echo "   📊 Badges After Event: {$newBadgeCount}\n";
        
        if ($newBadgeCount > $currentBadges) {
            echo "   🎉 SUCCESS! Badges increased from {$currentBadges} to {$newBadgeCount}\n";
            echo "   ✅ Automatic badge awarding is WORKING!\n";
        } else if ($newBadgeCount === $currentBadges) {
            echo "   ℹ️  Badge count unchanged. User might already have all available badges.\n";
            
            // Check AmaCredit activity
            $credit = $user->amaCredit()->first();
            if ($credit) {
                echo "   💰 AmaCredit Balance: {$credit->current_balance}\n";
                echo "   💰 Total Earned: {$credit->total_earned}\n";
                echo "   📅 Last Activity: {$credit->last_activity_at}\n";
            }
        }
    } catch (\Exception $e) {
        echo "   ❌ ERROR: {$e->getMessage()}\n";
        echo "   📍 File: {$e->getFile()}:{$e->getLine()}\n";
    }
} else {
    echo "   ⚠️  Cannot simulate event without an order\n";
    echo "   💡 Place an order from the mobile app to test automatic badges\n";
}

// Step 6: Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 SUMMARY\n";
echo str_repeat("=", 60) . "\n";
echo "Badge System Setup: " . ($badgeClassCount > 0 ? "✅ Ready" : "❌ Not Ready") . "\n";
echo "Event Listeners: " . (count($listeners) > 0 ? "✅ Registered" : "❌ Not Registered") . "\n";
echo "User Has Orders: " . ($latestOrder ? "✅ Yes" : "⚠️  No") . "\n";
echo "\n";

if ($badgeClassCount > 0 && count($listeners) > 0) {
    echo "✅ AUTOMATIC BADGE SYSTEM IS READY!\n";
    echo "💡 Place a new order from the mobile app to test it.\n";
    echo "🔍 Check badges in the profile tab after placing an order.\n";
} else {
    echo "❌ AUTOMATIC BADGE SYSTEM NEEDS SETUP!\n";
    if ($badgeClassCount === 0) {
        echo "   Run: php artisan db:seed --class=BadgeSystemSeeder\n";
    }
    if (count($listeners) === 0) {
        echo "   Run: php artisan event:cache or php artisan event:clear\n";
    }
}

echo "\n";

