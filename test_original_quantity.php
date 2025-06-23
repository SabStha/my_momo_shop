<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryOrder;
use App\Models\InventoryOrderItem;

echo "=== Original Quantity Test ===\n\n";

// Find an order with items
$order = InventoryOrder::with('items')->first();

if (!$order) {
    echo "❌ No orders found in database.\n";
    exit;
}

echo "✅ Found Order: {$order->order_number}\n";
echo "📊 Status: {$order->status}\n\n";

echo "=== Order Items Analysis ===\n";
foreach ($order->items as $item) {
    echo "📦 Item: {$item->item->name}\n";
    echo "   Original Quantity: {$item->original_quantity}\n";
    echo "   Current Quantity: {$item->quantity}\n";
    echo "   Unit Price: Rs. {$item->unit_price}\n";
    echo "   Total Price: Rs. {$item->total_price}\n";
    
    if ($item->original_quantity != $item->quantity) {
        echo "   ⚠️  QUANTITY CHANGED BY SUPPLIER!\n";
    } else {
        echo "   ✅ Quantity unchanged\n";
    }
    echo "\n";
}

echo "=== Modal Display Test ===\n";
echo "In the 'Confirm Receipt - Detailed' modal, you should see:\n";
echo "- Ordered Qty: Shows original_quantity (the initial order)\n";
echo "- Supplier Confirmed Qty: Shows current quantity (may be edited by supplier)\n";
echo "- Received Qty: Editable field (max = supplier confirmed qty)\n";
echo "- Yellow background: For rows where supplier changed the quantity\n\n";

echo "=== Test Instructions ===\n";
echo "1. Go to: http://localhost:8000/admin/inventory/orders/{$order->id}\n";
echo "2. Click 'Confirm Receipt' button\n";
echo "3. Check the modal table columns and highlighting\n";
echo "4. Verify that 'Ordered Qty' shows the original values\n\n";

echo "💡 The original_quantity field preserves the initial order quantities!\n";
echo "💡 This prevents confusion when suppliers make partial confirmations.\n"; 