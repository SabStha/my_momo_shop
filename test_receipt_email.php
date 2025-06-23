<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryOrder;
use App\Models\InventoryOrderItem;
use App\Mail\SupplierReceiptConfirmation;

echo "=== Receipt Confirmation Email Test ===\n\n";

// Find an order with supplier_confirmed status
$order = InventoryOrder::where('status', 'supplier_confirmed')->with('items.item')->first();

if (!$order) {
    echo "❌ No orders with 'supplier_confirmed' status found.\n";
    echo "💡 Create a test order and have the supplier confirm it first.\n";
    exit;
}

echo "✅ Found Order: {$order->order_number}\n";
echo "📊 Status: {$order->status}\n";
echo "📧 Supplier: {$order->supplier->name} ({$order->supplier->email})\n\n";

echo "=== Order Items ===\n";
foreach ($order->items as $item) {
    echo "📦 {$item->item->name}:\n";
    echo "   Original Ordered: {$item->original_quantity} {$item->item->unit}\n";
    echo "   Supplier Confirmed: {$item->quantity} {$item->item->unit}\n";
    echo "   Unit Price: Rs. {$item->unit_price}\n\n";
}

echo "=== Email Test Scenarios ===\n\n";

// Test 1: Full Receipt
echo "📧 Test 1: Full Receipt Email\n";
$fullReceiptData = [
    'received_quantities' => [],
    'receipt_notes' => 'All items received in perfect condition. Thank you!',
    'receipt_date' => now()->format('Y-m-d')
];

foreach ($order->items as $item) {
    $fullReceiptData['received_quantities'][$item->id] = $item->quantity;
}

echo "   ✅ All items received at full quantities\n";
echo "   📝 Notes: {$fullReceiptData['receipt_notes']}\n";
echo "   📅 Date: {$fullReceiptData['receipt_date']}\n\n";

// Test 2: Partial Receipt
echo "📧 Test 2: Partial Receipt Email\n";
$partialReceiptData = [
    'received_quantities' => [],
    'receipt_notes' => 'Some items were damaged during transport. Missing quantities need to be reordered.',
    'receipt_date' => now()->format('Y-m-d')
];

foreach ($order->items as $item) {
    // Receive only 70% of what was confirmed
    $partialReceiptData['received_quantities'][$item->id] = floor($item->quantity * 0.7);
}

echo "   ⚠️ Partial quantities received (70% of confirmed)\n";
echo "   📝 Notes: {$partialReceiptData['receipt_notes']}\n";
echo "   📅 Date: {$partialReceiptData['receipt_date']}\n\n";

echo "=== Email Content Preview ===\n";
echo "The emails will include:\n";
echo "✅ Full Receipt Email:\n";
echo "   - Green 'FULL ORDER RECEIVED' badge\n";
echo "   - Complete item list with quantities\n";
echo "   - Success message\n\n";

echo "⚠️ Partial Receipt Email:\n";
echo "   - Yellow 'PARTIAL ORDER RECEIVED' badge\n";
echo "   - Received items table\n";
echo "   - Missing items table with quantities to reorder\n";
echo "   - Total missing value calculation\n";
echo "   - Action required message\n\n";

echo "=== Test Instructions ===\n";
echo "1. Go to: http://localhost:8000/admin/inventory/orders/{$order->id}\n";
echo "2. Click 'Confirm Receipt' button\n";
echo "3. Enter quantities in the modal:\n";
echo "   - For full receipt: Use supplier confirmed quantities\n";
echo "   - For partial receipt: Use lower quantities\n";
echo "4. Add notes and submit\n";
echo "5. Check email at: {$order->supplier->email}\n\n";

echo "💡 The email will automatically detect if it's full or partial receipt!\n";
echo "💡 Missing items will be clearly listed for reordering from another supplier.\n";
echo "💡 Total missing value helps with budget planning.\n"; 