<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InventoryOrder;
use App\Models\Branch;
use App\Models\InventorySupplier;
use App\Models\InventoryItem;

echo "=== Branch Order Buttons Test ===\n\n";

// Find branches and supplier
$mainBranch = Branch::where('is_main', true)->first();
$regularBranch = Branch::where('is_main', false)->first();
$supplier = InventorySupplier::first();
$items = InventoryItem::take(2)->get();

if (!$mainBranch || !$regularBranch || !$supplier || $items->count() < 2) {
    echo "❌ Need main branch, regular branch, supplier, and items for testing\n";
    exit(1);
}

echo "✅ Found Main Branch: {$mainBranch->name}\n";
echo "✅ Found Regular Branch: {$regularBranch->name}\n";
echo "✅ Found Supplier: {$supplier->name}\n";
echo "✅ Found Items: " . $items->pluck('name')->implode(', ') . "\n\n";

// Create multiple branch orders for testing
echo "📦 Creating branch orders for testing...\n";

$orders = [];
for ($i = 1; $i <= 3; $i++) {
    $order = InventoryOrder::create([
        'order_number' => 'BTN-' . strtoupper(uniqid()),
        'supplier_id' => $mainBranch->id, // Main branch as supplier
        'branch_id' => $mainBranch->id, // Order goes to main branch
        'requesting_branch_id' => $regularBranch->id, // Requested by regular branch
        'status' => $i === 1 ? 'pending' : ($i === 2 ? 'received' : 'pending'),
        'order_date' => now(),
        'expected_delivery_date' => now()->addDays(7),
        'total_amount' => 0,
        'notes' => "Branch order test #{$i}"
    ]);

    // Add items
    $totalAmount = 0;
    foreach ($items as $item) {
        $quantity = 5 + $i; // Different quantities for each order
        $unitPrice = $item->unit_price;
        $totalPrice = $quantity * $unitPrice;
        $totalAmount += $totalPrice;
        
        $order->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => $quantity,
            'original_quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice
        ]);
    }

    $order->update(['total_amount' => $totalAmount]);
    $orders[] = $order;
    
    echo "  ✅ Created Order #{$i}: {$order->order_number} (Status: {$order->status})\n";
}

echo "\n=== Test Results ===\n";
echo "📋 Created " . count($orders) . " branch orders\n";
echo "🏢 Requesting Branch: {$regularBranch->name}\n";
echo "🏢 Processing Branch: {$mainBranch->name}\n\n";

// Test the branch orders query logic
echo "🧪 Testing branch orders query logic...\n";

// For main branch view
$mainBranchOrders = InventoryOrder::where('requesting_branch_id', '!=', null)
    ->where('requesting_branch_id', '!=', $mainBranch->id)
    ->where('branch_id', $mainBranch->id)
    ->with(['requestingBranch', 'supplier'])
    ->orderBy('created_at', 'desc')
    ->get()
    ->groupBy('requesting_branch_id');

echo "📊 Main branch view - Branch orders found: " . $mainBranchOrders->count() . "\n";
foreach ($mainBranchOrders as $branchId => $branchOrders) {
    $branch = $branchOrders->first()->requestingBranch;
    $pendingCount = $branchOrders->where('status', 'pending')->count();
    echo "  🏢 {$branch->name}: {$branchOrders->count()} orders ({$pendingCount} pending)\n";
}

// For regular branch view
$regularBranchOrders = InventoryOrder::where('requesting_branch_id', $regularBranch->id)
    ->with(['requestingBranch', 'supplier'])
    ->orderBy('created_at', 'desc')
    ->get()
    ->groupBy('requesting_branch_id');

echo "\n📊 Regular branch view - Branch orders found: " . $regularBranchOrders->count() . "\n";
foreach ($regularBranchOrders as $branchId => $branchOrders) {
    $branch = $branchOrders->first()->requestingBranch;
    $pendingCount = $branchOrders->where('status', 'pending')->count();
    echo "  🏢 {$branch->name}: {$branchOrders->count()} orders ({$pendingCount} pending)\n";
}

echo "\n=== Test Complete ===\n";
echo "🔗 Main Branch Orders Page: http://localhost:8000/admin/inventory/orders?branch={$mainBranch->id}\n";
echo "🔗 Regular Branch Orders Page: http://localhost:8000/admin/inventory/orders?branch={$regularBranch->id}\n\n";

echo "💡 Expected Results:\n";
echo "   1. Main branch page should show 'Branch Orders to Process' section\n";
echo "   2. Regular branch page should show 'My Branch Orders' section\n";
echo "   3. Each branch should have a card with order count and status\n";
echo "   4. 'View Latest' button should link to the most recent order\n";
echo "   5. 'All (X)' button should show modal with all orders for that branch\n";
echo "   6. Pending orders should be highlighted with warning indicators\n";

// Clean up test orders
echo "\n🧹 Cleaning up test orders...\n";
foreach ($orders as $order) {
    $order->items()->delete();
    $order->delete();
}
echo "✅ Test orders cleaned up\n"; 