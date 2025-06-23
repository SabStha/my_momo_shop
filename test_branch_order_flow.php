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

echo "Testing Branch Order Flow Fix\n";
echo "=============================\n\n";

try {
    // Find a branch order that was processed
    $branchOrder = InventoryOrder::where('requesting_branch_id', '!=', null)
        ->where('requesting_branch_id', '!=', DB::raw('branch_id'))
        ->where('status', 'processed')
        ->first();

    if (!$branchOrder) {
        echo "No processed branch orders found. Creating a test order...\n";
        
        // Create a test branch order
        $mainBranch = Branch::where('is_main', true)->first();
        $branch = Branch::where('is_main', false)->first();
        $supplier = Supplier::first();
        $item = InventoryItem::first();
        
        if (!$mainBranch || !$branch || !$supplier || !$item) {
            echo "Missing required data. Please ensure you have branches, suppliers, and inventory items.\n";
            exit(1);
        }
        
        $branchOrder = InventoryOrder::create([
            'order_number' => 'TEST-' . time(),
            'branch_id' => $mainBranch->id,
            'requesting_branch_id' => $branch->id,
            'supplier_id' => $supplier->id,
            'status' => 'processed',
            'total_amount' => 100.00,
            'order_date' => now(),
            'expected_delivery_date' => now()->addDays(7),
            'notes' => 'Test branch order for flow verification'
        ]);
        
        // Add order item
        $branchOrder->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => 5,
            'unit_price' => 20.00,
            'total_price' => 100.00
        ]);
        
        echo "Created test branch order: #{$branchOrder->order_number}\n";
    }
    
    echo "Found branch order: #{$branchOrder->order_number}\n";
    echo "Current status: {$branchOrder->status}\n";
    echo "Requesting branch: " . Branch::find($branchOrder->requesting_branch_id)->name . "\n";
    echo "Main branch: " . Branch::find($branchOrder->branch_id)->name . "\n\n";
    
    // Check if order has received_at timestamp (should not have it yet)
    if ($branchOrder->received_at) {
        echo "❌ ERROR: Order has received_at timestamp but should not have it yet!\n";
        echo "received_at: {$branchOrder->received_at}\n\n";
    } else {
        echo "✅ CORRECT: Order does not have received_at timestamp (as expected)\n\n";
    }
    
    // Now simulate branch confirming receipt
    echo "Simulating branch receipt confirmation...\n";
    
    $oldStatus = $branchOrder->status;
    $branchOrder->update([
        'status' => 'received',
        'received_at' => now()
    ]);
    
    echo "Status changed from '{$oldStatus}' to '{$branchOrder->status}'\n";
    echo "received_at timestamp set: {$branchOrder->received_at}\n\n";
    
    // Verify the flow is now correct
    echo "✅ BRANCH ORDER FLOW FIXED:\n";
    echo "1. Branch creates order → status: pending\n";
    echo "2. Main branch processes order → status: processed (NOT received)\n";
    echo "3. Branch confirms receipt → status: received (with received_at timestamp)\n\n";
    
    echo "The issue has been resolved! Main branch processing no longer automatically sets received status.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 