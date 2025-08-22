<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\Branch;

echo "=== DEBUGGING ORDER ISSUES ===\n\n";

// Check recent orders
echo "1. Recent Orders:\n";
$recentOrders = Order::orderBy('created_at', 'desc')->take(5)->get();
foreach ($recentOrders as $order) {
    echo "ID: {$order->id}, Type: {$order->order_type}, Status: {$order->status}, Branch: {$order->branch_id}, Email: {$order->customer_email}\n";
}

echo "\n2. Online Orders (order_type = 'online'):\n";
$onlineOrders = Order::where('order_type', 'online')->orderBy('created_at', 'desc')->take(10)->get();
foreach ($onlineOrders as $order) {
    echo "ID: {$order->id}, Status: {$order->status}, Branch: {$order->branch_id}, Email: {$order->customer_email}\n";
}

echo "\n3. Orders for Branch 2 (not completed):\n";
$branch2Orders = Order::where('branch_id', 2)
    ->where('status', '!=', 'completed')
    ->orderBy('created_at', 'desc')
    ->get();
foreach ($branch2Orders as $order) {
    echo "ID: {$order->id}, Type: {$order->order_type}, Status: {$order->status}, Email: {$order->customer_email}\n";
}

echo "\n4. Online Orders for Branch 2:\n";
$onlineBranch2Orders = Order::where('order_type', 'online')
    ->where('branch_id', 2)
    ->where('status', '!=', 'completed')
    ->orderBy('created_at', 'desc')
    ->get();
foreach ($onlineBranch2Orders as $order) {
    echo "ID: {$order->id}, Status: {$order->status}, Email: {$order->customer_email}\n";
}

echo "\n5. Available Branches:\n";
$branches = Branch::all();
foreach ($branches as $branch) {
    echo "ID: {$branch->id}, Name: {$branch->name}\n";
}

echo "\n6. Testing Payment Manager Query:\n";
// Simulate the exact query from AdminOrderController
$paymentManagerOrders = Order::with(['items', 'branch'])
    ->where('status', '!=', 'completed')
    ->where('branch_id', 2)
    ->orderBy('created_at', 'desc')
    ->get();

echo "Orders that should appear in payment manager for branch 2: " . $paymentManagerOrders->count() . "\n";
foreach ($paymentManagerOrders as $order) {
    echo "ID: {$order->id}, Type: {$order->order_type}, Status: {$order->status}, Email: {$order->customer_email}\n";
} 