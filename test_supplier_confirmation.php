<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryOrder;

echo "=== Supplier Confirmation Test ===\n\n";

// Get order #16
$order = InventoryOrder::find(16);
if (!$order) {
    echo "❌ Order #16 not found\n";
    exit;
}

echo "✅ Order #16 found\n";
echo "📋 Order Number: {$order->order_number}\n";
echo "📊 Current Status: {$order->status}\n";
echo "📧 Supplier: {$order->supplier->name}\n";

// Check if supplier_confirmed_at field exists
$fillable = $order->getFillable();
echo "📝 Fillable fields: " . implode(', ', $fillable) . "\n";

// Check if supplier_confirmed_at is in fillable
if (in_array('supplier_confirmed_at', $fillable)) {
    echo "✅ supplier_confirmed_at is in fillable fields\n";
} else {
    echo "❌ supplier_confirmed_at is NOT in fillable fields\n";
}

// Test the update
try {
    echo "\n🧪 Testing update to supplier_confirmed...\n";
    
    $result = $order->update([
        'status' => 'supplier_confirmed',
        'supplier_confirmed_at' => now()
    ]);
    
    if ($result) {
        echo "✅ Update successful!\n";
        echo "📊 New status: {$order->status}\n";
        echo "📅 Supplier confirmed at: {$order->supplier_confirmed_at}\n";
    } else {
        echo "❌ Update failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error during update: " . $e->getMessage() . "\n";
    echo "📋 Error details: " . $e->getTraceAsString() . "\n";
} 