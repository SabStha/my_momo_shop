<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use Illuminate\Support\Facades\DB;

echo "🧪 Testing Sales Analytics Query\n";
echo str_repeat("=", 60) . "\n\n";

$startDate = now()->subMonths(3);
$endDate = now();
$branchId = 1;

echo "📅 Date Range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n";
echo "🏢 Branch ID: {$branchId}\n\n";

// Test 1: All delivered orders
echo "Test 1: All delivered orders (any branch)\n";
$allDelivered = Order::whereIn('status', ['completed', 'delivered'])->get(['id', 'branch_id', 'status', 'total', 'created_at']);
echo "   Total: " . $allDelivered->count() . "\n";
echo "   Total Sales: Rs " . $allDelivered->sum('total') . "\n\n";

// Test 2: Delivered orders for branch 1
echo "Test 2: Delivered orders for branch 1\n";
$branch1Delivered = Order::whereIn('status', ['completed', 'delivered'])
    ->where('branch_id', $branchId)
    ->get(['id', 'branch_id', 'status', 'total', 'created_at']);
echo "   Total: " . $branch1Delivered->count() . "\n";
echo "   Total Sales: Rs " . $branch1Delivered->sum('total') . "\n\n";

// Test 3: With date range
echo "Test 3: Branch 1 orders with date range\n";
$summary = Order::whereBetween('created_at', [$startDate, $endDate])
    ->whereIn('status', ['completed', 'delivered'])
    ->where('branch_id', $branchId)
    ->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(total), 0) as total_sales, COUNT(DISTINCT user_id) as unique_customers, COALESCE(AVG(total), 0) as avg_order')
    ->first();

echo "   Total Orders: " . $summary->total_orders . "\n";
echo "   Total Sales: Rs " . $summary->total_sales . "\n";
echo "   Unique Customers: " . $summary->unique_customers . "\n";
echo "   Average Order: Rs " . round($summary->avg_order, 2) . "\n\n";

// Test 4: Sample orders
echo "Test 4: Sample delivered orders for branch 1\n";
$samples = Order::whereIn('status', ['completed', 'delivered'])
    ->where('branch_id', $branchId)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['id', 'status', 'total', 'created_at']);

foreach ($samples as $order) {
    echo "   Order #{$order->id}: Rs {$order->total} ({$order->status}) - {$order->created_at->format('Y-m-d H:i')}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ Test Complete!\n\n";

