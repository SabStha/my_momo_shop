<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InventoryOrder;
use App\Models\Branch;

echo "=== Branch Order Color Test ===\n\n";

// Find main branch
$mainBranch = Branch::where('is_main', true)->first();
if (!$mainBranch) {
    echo "❌ Main branch not found!\n";
    exit;
}

// Find a regular branch
$regularBranch = Branch::where('is_main', false)->first();
if (!$regularBranch) {
    echo "❌ Regular branch not found!\n";
    exit;
}

echo "✅ Main Branch: {$mainBranch->name}\n";
echo "✅ Regular Branch: {$regularBranch->name}\n\n";

// Find orders
$orders = InventoryOrder::with(['branch', 'requestingBranch', 'supplier'])
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

echo "=== Recent Orders Analysis ===\n";
foreach ($orders as $order) {
    $isBranchOrder = $order->requesting_branch_id && $order->requesting_branch_id != $order->branch_id;
    
    echo "📋 Order #{$order->order_number}\n";
    echo "   Status: {$order->status}\n";
    echo "   Branch: {$order->branch->name}\n";
    echo "   Requesting Branch: " . ($order->requestingBranch ? $order->requestingBranch->name : 'None') . "\n";
    echo "   Supplier: {$order->supplier->name}\n";
    echo "   Is Branch Order: " . ($isBranchOrder ? '✅ YES' : '❌ NO') . "\n";
    
    if ($isBranchOrder) {
        echo "   🎨 Should show: GREEN colors (Auto-Sent)\n";
        echo "   📧 Email sent to: evanhuc404@gmail.com\n";
    } else {
        echo "   🎨 Should show: BLUE/YELLOW colors (Manual)\n";
        echo "   📧 Email sent to: {$order->supplier->email}\n";
    }
    echo "\n";
}

echo "=== Test Complete ===\n";
echo "💡 Check the following pages to verify colors:\n";
echo "   1. Order details page: http://localhost:8000/admin/inventory/orders/{$orders->first()->id}\n";
echo "   2. Supplier view: http://localhost:8000/admin/inventory/orders/supplier-view\n";
echo "   3. Orders list: http://localhost:8000/admin/inventory/orders\n"; 