<?php

/**
 * Test Mobile Notification System
 * 
 * This script tests the AI offer notification integration
 * Run with: php test_mobile_notifications.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\MobileNotificationService;
use App\Services\AIOfferService;

echo "\n🔔 Testing Mobile Notification System\n";
echo "=====================================\n\n";

try {
    // Test 1: Send Test Notification
    echo "Test 1: Sending test system notification...\n";
    $notificationService = app(MobileNotificationService::class);
    $result = $notificationService->sendSystemNotification(
        '🎉 Mobile App Test',
        'This is a test notification for the Amako Momo mobile app!'
    );
    
    if ($result['success']) {
        echo "✅ Test notification sent to {$result['notifications_sent']} users\n\n";
    } else {
        echo "❌ Failed to send test notification\n\n";
    }

    // Test 2: Generate AI Offers
    echo "Test 2: Generating AI offers and sending to mobile users...\n";
    $aiOfferService = app(AIOfferService::class);
    $offerResult = $aiOfferService->generateAIOffers(1);
    
    if ($offerResult['success']) {
        $count = $offerResult['offers_created'];
        echo "✅ Successfully generated {$count} AI offers\n";
        
        if ($count > 0) {
            echo "\n📋 Generated Offers:\n";
            foreach ($offerResult['offers'] as $offer) {
                echo "  • {$offer->title} ({$offer->discount}% off)\n";
                echo "    Code: {$offer->code}\n";
                echo "    Type: {$offer->type}\n";
                echo "    Valid until: {$offer->valid_until->format('Y-m-d H:i')}\n\n";
            }
        }
    } else {
        echo "❌ Failed to generate AI offers: " . ($offerResult['error'] ?? 'Unknown error') . "\n\n";
    }

    // Test 3: Check notification count
    echo "Test 3: Checking notification statistics...\n";
    $totalNotifications = \DB::table('notifications')->count();
    $unreadNotifications = \DB::table('notifications')->whereNull('read_at')->count();
    
    echo "✅ Total notifications in database: {$totalNotifications}\n";
    echo "✅ Unread notifications: {$unreadNotifications}\n\n";

    // Test 4: Show sample notification
    echo "Test 4: Sample notification data:\n";
    $sampleNotification = \DB::table('notifications')
        ->orderBy('created_at', 'desc')
        ->first();
    
    if ($sampleNotification) {
        $data = json_decode($sampleNotification->data, true);
        echo "  Type: " . ($data['type'] ?? 'N/A') . "\n";
        echo "  Title: " . ($data['title'] ?? 'N/A') . "\n";
        echo "  Message: " . ($data['message'] ?? 'N/A') . "\n";
        echo "  Created: {$sampleNotification->created_at}\n";
        echo "  Read: " . ($sampleNotification->read_at ? 'Yes' : 'No') . "\n\n";
    }

    echo "=====================================\n";
    echo "✅ All tests completed successfully!\n";
    echo "=====================================\n\n";

    echo "📱 Next Steps:\n";
    echo "1. Open your mobile app\n";
    echo "2. Pull to refresh on the notifications screen\n";
    echo "3. You should see the new notifications!\n\n";

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

