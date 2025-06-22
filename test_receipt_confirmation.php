<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryOrder;

echo "=== Receipt Confirmation Test ===\n\n";

// Find an order with supplier_confirmed status
$order = InventoryOrder::with(['items.item', 'supplier'])->where('status', 'supplier_confirmed')->first();

if (!$order) {
    echo "❌ No orders with 'supplier_confirmed' status found.\n";
    echo "Please create an order and set its status to 'supplier_confirmed' first.\n";
    exit;
}

echo "✅ Found Order: {$order->order_number}\n";
echo "📊 Current Status: {$order->status}\n";
echo "📧 Supplier: {$order->supplier->name} ({$order->supplier->email})\n";
echo "🔗 Order URL: http://localhost:8000/admin/inventory/orders/{$order->id}\n\n";

echo "=== Test Data ===\n";
$testData = [
    'received_quantities' => [],
    'receipt_notes' => 'Test receipt confirmation via script',
    'receipt_date' => now()->format('Y-m-d')
];

foreach ($order->items as $item) {
    $itemName = $item->item ? $item->item->name : 'Unknown Item';
    $testData['received_quantities'][$item->id] = $item->quantity; // Receive full quantity
    echo "📦 {$itemName}: {$item->quantity} units\n";
}

echo "\n=== Instructions ===\n";
echo "1. Go to the order URL above\n";
echo "2. Click 'Confirm Receipt' button\n";
echo "3. Fill in the modal form\n";
echo "4. Click 'Save & Confirm'\n";
echo "5. Check browser console (F12) for debug logs\n";
echo "6. Check email at: {$order->supplier->email}\n\n";

echo "=== Expected Behavior ===\n";
echo "✅ Modal should open when clicking 'Confirm Receipt'\n";
echo "✅ Form should submit via AJAX (no page reload)\n";
echo "✅ Success message should appear\n";
echo "✅ Email should be sent to supplier\n";
echo "✅ Page should reload after 1.5 seconds\n\n";

echo "=== Debug Steps ===\n";
echo "1. Open browser console (F12 → Console tab)\n";
echo "2. Look for these debug messages:\n";
echo "   - 'Form found, adding event listener'\n";
echo "   - 'Form submitted!'\n";
echo "   - 'Submitting data: {...}'\n";
echo "   - 'Response status: 200'\n";
echo "   - 'Response data: {...}'\n\n";

echo "If you don't see these messages, there's a JavaScript issue.\n";
echo "If you see error messages, there's a backend issue.\n"; 