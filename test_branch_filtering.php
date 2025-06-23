<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryOrder;
use App\Models\Branch;

echo "Testing Branch Filtering for Order #42\n";
echo "=====================================\n\n";

try {
    // Get order #42
    $order = InventoryOrder::with(['branch', 'requestingBranch', 'supplier'])->find(42);
    
    if (!$order) {
        echo "❌ Order #42 not found!\n";
        exit(1);
    }
    
    echo "Order #42 Details:\n";
    echo "Order Number: {$order->order_number}\n";
    echo "Status: {$order->status}\n";
    echo "Branch ID: {$order->branch_id} ({$order->branch->name})\n";
    echo "Requesting Branch ID: {$order->requesting_branch_id} ({$order->requestingBranch->name})\n\n";
    
    echo "Testing Branch Filtering:\n";
    echo "========================\n\n";
    
    // Test 1: Branch view (South Branch)
    echo "1. Branch View (South Branch):\n";
    echo "   URL: http://localhost:8000/admin/inventory/orders/42?branch=3\n";
    echo "   Expected filtering:\n";
    echo "   ✅ Order Actions section: HIDDEN (branches don't need main branch actions)\n";
    echo "   ✅ Supplier Overview: HIDDEN (branches don't need supplier info)\n";
    echo "   ✅ Recent Orders from Supplier: HIDDEN (branches don't need supplier history)\n";
    echo "   ✅ Edit/Delete buttons: HIDDEN (branches can't edit/delete orders)\n";
    echo "   ✅ Supplier confirmed activity: HIDDEN (branches don't need supplier confirmations)\n";
    echo "   ✅ Activity Timeline: SHOWN (but filtered for branch context)\n";
    echo "   ✅ Order Details: SHOWN\n";
    echo "   ✅ Order Items: SHOWN\n\n";
    
    // Test 2: Main branch view
    echo "2. Main Branch View:\n";
    echo "   URL: http://localhost:8000/admin/inventory/orders/42?branch=1\n";
    echo "   Expected filtering:\n";
    echo "   ✅ Order Actions section: SHOWN (main branch can process branch orders)\n";
    echo "   ✅ Supplier Overview: SHOWN (main branch needs supplier info)\n";
    echo "   ✅ Recent Orders from Supplier: SHOWN (main branch needs supplier history)\n";
    echo "   ✅ Edit/Delete buttons: SHOWN (main branch can edit/delete if pending)\n";
    echo "   ✅ Supplier confirmed activity: SHOWN (main branch needs supplier confirmations)\n";
    echo "   ✅ Activity Timeline: SHOWN (full timeline)\n";
    echo "   ✅ Order Details: SHOWN\n";
    echo "   ✅ Order Items: SHOWN\n\n";
    
    echo "How to test:\n";
    echo "===========\n";
    echo "1. Go to: http://localhost:8000/admin/inventory/orders/42?branch=3\n";
    echo "   Should show: Clean branch view without supplier info or main branch actions\n\n";
    
    echo "2. Go to: http://localhost:8000/admin/inventory/orders/42?branch=1\n";
    echo "   Should show: Full main branch view with all actions and supplier info\n\n";
    
    echo "3. Compare the two views - the branch view should be much cleaner and focused\n";
    echo "   on what the branch needs to know about their order.\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 