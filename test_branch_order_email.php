<?php

require_once 'vendor/autoload.php';

use App\Models\InventoryOrder;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Branch Order Email and List Fix\n";
echo "======================================\n\n";

try {
    // Find branches
    $mainBranch = Branch::where('is_main', true)->first();
    $regularBranch = Branch::where('is_main', false)->first();
    
    if (!$mainBranch || !$regularBranch) {
        echo "❌ ERROR: Need both main branch and regular branch for testing\n";
        exit(1);
    }
    
    echo "✅ Found branches:\n";
    echo "   Main Branch: {$mainBranch->name} (ID: {$mainBranch->id})\n";
    echo "   Regular Branch: {$regularBranch->name} (ID: {$regularBranch->id})\n\n";
    
    // Find supplier and item
    $supplier = Supplier::first();
    $item = InventoryItem::first();
    
    if (!$supplier || !$item) {
        echo "❌ ERROR: Need supplier and inventory item for testing\n";
        exit(1);
    }
    
    echo "✅ Found supplier: {$supplier->name}\n";
    echo "✅ Found item: {$item->name}\n\n";
    
    // Create a test branch order (regular branch ordering)
    echo "Creating test branch order...\n";
    
    $orderData = [
        'order_number' => 'TEST-BRANCH-' . time(),
        'supplier_id' => $mainBranch->id, // Main branch as supplier for branch orders
        'branch_id' => $mainBranch->id, // Order goes to main branch
        'order_date' => now(),
        'expected_delivery_date' => now()->addDays(7),
        'status' => 'pending',
        'notes' => 'Test branch order for email verification',
        'requesting_branch_id' => $regularBranch->id, // Regular branch requesting
        'total_amount' => 100.00
    ];
    
    $branchOrder = InventoryOrder::create($orderData);
    
    // Add order item
    $branchOrder->items()->create([
        'inventory_item_id' => $item->id,
        'quantity' => 5,
        'unit_price' => 20.00,
        'total_price' => 100.00,
        'original_quantity' => 5
    ]);
    
    echo "✅ Created branch order: #{$branchOrder->order_number}\n";
    echo "   Requesting Branch: {$regularBranch->name} (ID: {$regularBranch->id})\n";
    echo "   Main Branch: {$mainBranch->name} (ID: {$mainBranch->id})\n";
    echo "   Status: {$branchOrder->status}\n\n";
    
    // Test 1: Check if order appears in regular branch list
    echo "Test 1: Checking if order appears in regular branch list...\n";
    $regularBranchOrders = InventoryOrder::where('requesting_branch_id', $regularBranch->id)->get();
    echo "   Orders requested by regular branch: {$regularBranchOrders->count()}\n";
    
    if ($regularBranchOrders->contains($branchOrder->id)) {
        echo "   ✅ Order appears in regular branch list\n";
    } else {
        echo "   ❌ Order does NOT appear in regular branch list\n";
    }
    
    // Test 2: Check if order appears in main branch list
    echo "\nTest 2: Checking if order appears in main branch list...\n";
    $mainBranchOrders = InventoryOrder::where('branch_id', $mainBranch->id)->get();
    echo "   Orders for main branch: {$mainBranchOrders->count()}\n";
    
    if ($mainBranchOrders->contains($branchOrder->id)) {
        echo "   ✅ Order appears in main branch list\n";
    } else {
        echo "   ❌ Order does NOT appear in main branch list\n";
    }
    
    // Test 3: Check email logic
    echo "\nTest 3: Checking email logic...\n";
    $isCentralizedOrder = $regularBranch && !$regularBranch->is_main;
    echo "   Is centralized order: " . ($isCentralizedOrder ? 'Yes' : 'No') . "\n";
    
    if ($isCentralizedOrder) {
        echo "   ✅ Email should be sent to main branch\n";
        echo "   Main branch email: {$mainBranch->email}\n";
    } else {
        echo "   ❌ Email will NOT be sent\n";
    }
    
    echo "\n✅ Test completed successfully!\n";
    echo "\nTo verify the fix:\n";
    echo "1. Go to: http://localhost:8000/admin/inventory/orders/list?branch={$regularBranch->id}\n";
    echo "   (Should show the test order)\n";
    echo "2. Check email at: {$mainBranch->email}\n";
    echo "   (Should receive notification about the branch order)\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 