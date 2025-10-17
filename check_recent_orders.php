<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\CashDrawerSession;

echo "🔍 Checking Recent Orders and Sessions\n";
echo "=======================================\n\n";

// Check orders created in last hour
$oneHourAgo = now()->subHour();
$recentOrders = Order::where('created_at', '>=', $oneHourAgo)
    ->orderBy('created_at', 'desc')
    ->get();

echo "📦 Orders in last hour: " . $recentOrders->count() . "\n\n";

if ($recentOrders->count() > 0) {
    foreach ($recentOrders as $order) {
        echo "Order #{$order->order_number}\n";
        echo "  ID: {$order->id}\n";
        echo "  Type: {$order->order_type}\n";
        echo "  Branch: {$order->branch_id}\n";
        echo "  Payment: {$order->payment_method}\n";
        echo "  Total: Rs. {$order->total_amount}\n";
        echo "  Status: {$order->status}\n";
        echo "  Created: {$order->created_at}\n";
        echo "  ---\n";
    }
} else {
    echo "❌ No orders in last hour\n";
}

echo "\n";

// Check all orders today
$today = now()->startOfDay();
$todayOrders = Order::where('created_at', '>=', $today)
    ->orderBy('created_at', 'desc')
    ->get();

echo "📊 Total orders today: " . $todayOrders->count() . "\n\n";

// Check current sessions
$openSessions = CashDrawerSession::whereNull('closed_at')->get();
echo "💰 Currently open sessions: " . $openSessions->count() . "\n";

if ($openSessions->count() > 0) {
    foreach ($openSessions as $session) {
        echo "  Branch {$session->branch_id} - Opened at {$session->opened_at}\n";
    }
} else {
    echo "  ❌ No open sessions\n";
}

echo "\n";

// Check if there are ANY orders with amako_credits payment
$amakoOrders = Order::where('payment_method', 'amako_credits')
    ->orWhere('payment_method', 'wallet')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

echo "💰 Recent Amako Credits/Wallet orders: " . $amakoOrders->count() . "\n";
if ($amakoOrders->count() > 0) {
    foreach ($amakoOrders as $order) {
        echo "  #{$order->order_number} - Rs. {$order->total_amount} - {$order->created_at}\n";
    }
}

