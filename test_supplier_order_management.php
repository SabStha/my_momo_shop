<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryOrder;
use App\Models\InventorySupplier;
use App\Models\Branch;
use App\Models\InventoryItem;

echo "=== Supplier Order Management System Test ===\n\n";

// Find a test order
$order = InventoryOrder::where('status', 'sent')->first();

if (!$order) {
    echo "❌ No orders with 'sent' status found. Creating a test order...\n";
    
    $mainBranch = Branch::where('name', 'Main Branch')->first();
    $supplier = InventorySupplier::where('email', 'sabstha98@gmail.com')->first();
    $items = InventoryItem::take(2)->get();
    
    if (!$mainBranch || !$supplier || $items->isEmpty()) {
        echo "❌ Missing required data. Please check your database.\n";
        exit;
    }
    
    // Create a test order
    $order = InventoryOrder::create([
        'order_number' => 'INV-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        'supplier_id' => $supplier->id,
        'branch_id' => $mainBranch->id,
        'requesting_branch_id' => $mainBranch->id,
        'status' => 'sent',
        'order_date' => now(),
        'expected_delivery_date' => now()->addDays(7),
        'total_amount' => 0,
        'notes' => 'Test order for supplier management system',
        'sent_at' => now()
    ]);
    
    // Add items
    $totalAmount = 0;
    foreach ($items as $item) {
        $quantity = rand(10, 50);
        $unitPrice = rand(100, 500);
        $totalPrice = $quantity * $unitPrice;
        $totalAmount += $totalPrice;
        
        $order->items()->create([
            'inventory_item_id' => $item->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice
        ]);
        
        echo "📦 Added: {$item->name} - {$quantity} units @ Rs. {$unitPrice}\n";
    }
    
    $order->update(['total_amount' => $totalAmount]);
    echo "💰 Total Amount: Rs. {$totalAmount}\n";
}

echo "✅ Found Order: {$order->order_number}\n";
echo "📊 Current Status: {$order->status}\n";
echo "📧 Supplier: {$order->supplier->name} ({$order->supplier->email})\n";
echo "📅 Created: {$order->created_at->format('Y-m-d H:i:s')}\n\n";

// Generate the supplier view link
$token = hash('sha256', $order->id . $order->created_at . config('app.key'));
$supplierViewUrl = "http://localhost:8000/supplier/order/{$order->id}/view?token={$token}";

echo "🔗 Supplier Order Management Link:\n";
echo "{$supplierViewUrl}\n\n";

echo "=== Available Actions for Supplier ===\n";
echo "1. ✅ Confirm Full Order: If supplier has all items in stock\n";
echo "2. ⚠️ Partial Confirmation: If supplier can only provide some items/quantities\n";
echo "3. ❌ Reject Order: If supplier cannot fulfill the order\n\n";

echo "=== Instructions ===\n";
echo "1. Copy the supplier link above\n";
echo "2. Open it in your browser\n";
echo "3. You'll see the new supplier order management interface\n";
echo "4. Test each action:\n";
echo "   - Full confirmation with delivery date and notes\n";
echo "   - Partial confirmation with available quantities\n";
echo "   - Rejection with reason and notes\n";
echo "5. Check the order status changes in the admin panel\n\n";

echo "=== Expected Workflow ===\n";
echo "📤 Admin sends order → 📧 Supplier receives email → 🔗 Supplier clicks link\n";
echo "📋 Supplier reviews order → ✅/⚠️/❌ Supplier takes action → 📊 Status updates\n";
echo "📧 Admin receives notification → 🔍 Admin reviews → ✅ Admin confirms receipt\n\n";

echo "💡 This new system gives suppliers full control over order management!\n";
echo "💡 No more orders stuck in 'sent' status when suppliers can't fulfill them.\n";
echo "💡 Suppliers can communicate availability and delivery dates.\n";
echo "💡 Admin gets clear feedback on what's available and when.\n"; 