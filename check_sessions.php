<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CashDrawerSession;
use App\Models\Order;

echo "🔍 Checking Cash Drawer Sessions\n";
echo "=================================\n\n";

// Check open sessions
$openSessions = CashDrawerSession::whereNull('closed_at')->get();

echo "📊 Open Sessions: " . $openSessions->count() . "\n\n";

if ($openSessions->count() > 0) {
    echo "Open Sessions Details:\n";
    foreach ($openSessions as $session) {
        echo "  Branch ID: {$session->branch_id}\n";
        echo "  Opened at: {$session->opened_at}\n";
        echo "  Opened by: {$session->opened_by}\n";
        echo "  ---\n";
    }
} else {
    echo "❌ No open sessions found\n";
}

echo "\n";

// Check recent orders from mobile
$recentOrders = Order::where('order_type', 'online')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

echo "📦 Recent Online Orders: " . $recentOrders->count() . "\n\n";

if ($recentOrders->count() > 0) {
    echo "Recent Orders Details:\n";
    foreach ($recentOrders as $order) {
        echo "  Order: {$order->order_number}\n";
        echo "  Branch ID: {$order->branch_id}\n";
        echo "  Status: {$order->status}\n";
        echo "  Created: {$order->created_at}\n";
        echo "  ---\n";
    }
}

echo "\n";

// Check all sessions (including closed)
$allSessions = CashDrawerSession::orderBy('created_at', 'desc')->limit(5)->get();
echo "📋 Last 5 Sessions (All):\n";
foreach ($allSessions as $session) {
    $status = $session->closed_at ? '❌ Closed' : '✅ Open';
    echo "  {$status} - Branch {$session->branch_id}\n";
    echo "  Opened: {$session->opened_at}\n";
    if ($session->closed_at) {
        echo "  Closed: {$session->closed_at}\n";
    }
    echo "  ---\n";
}

