<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "🔔 Creating Sample Notifications for Mobile App\n";
echo "================================================\n\n";

try {
    // Get all users (remove is_active check as column doesn't exist)
    $users = User::limit(10)->get(); // Limit to 10 users for testing
    
    if ($users->isEmpty()) {
        echo "❌ No users found! Please create at least one user.\n";
        exit(1);
    }
    
    echo "✅ Found {$users->count()} active users\n\n";
    
    // Sample notification data
    $notifications = [
        [
            'type' => 'promotion',
            'title' => '🎁 Special Weekend Offer!',
            'message' => 'Get 20% off on all momo orders this weekend! Use code: WEEKEND20',
            'data' => [
                'offer_code' => 'WEEKEND20',
                'discount' => 20,
                'action' => 'view_offer',
                'navigation' => '/menu',
            ]
        ],
        [
            'type' => 'promotion',
            'title' => '⚡ Flash Sale - Limited Time!',
            'message' => 'Buy 2 plates, get 1 free! Only for the next 2 hours.',
            'data' => [
                'offer_type' => 'flash_sale',
                'action' => 'view_menu',
                'navigation' => '/menu',
            ]
        ],
        [
            'type' => 'system',
            'title' => '🎉 Welcome to Amako Momo App!',
            'message' => 'Thank you for downloading our app. Check out our special offers!',
            'data' => [
                'action' => 'view_home',
                'navigation' => '/',
            ]
        ],
    ];
    
    $totalCreated = 0;
    
    foreach ($users as $user) {
        foreach ($notifications as $notificationData) {
            $notification = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => $notificationData['type'],
                'title' => $notificationData['title'],
                'message' => $notificationData['message'],
                'data' => $notificationData['data'],
                'created_at' => now(),
                'read_at' => null,
            ];
            
            DB::table('notifications')->insert([
                'id' => $notification['id'],
                'type' => 'App\\Notifications\\OfferNotification',
                'notifiable_type' => 'App\\Models\\User',
                'notifiable_id' => $user->id,
                'data' => json_encode($notification),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $totalCreated++;
        }
    }
    
    echo "✅ Successfully created {$totalCreated} notifications!\n";
    echo "   ({$users->count()} users × " . count($notifications) . " notifications each)\n\n";
    
    // Show statistics
    $total = DB::table('notifications')->count();
    $unread = DB::table('notifications')->whereNull('read_at')->count();
    
    echo "📊 Notification Statistics:\n";
    echo "   Total notifications: {$total}\n";
    echo "   Unread: {$unread}\n\n";
    
    echo "================================================\n";
    echo "✅ Done! Open your mobile app and check notifications!\n";
    echo "================================================\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

