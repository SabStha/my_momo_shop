<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventorySupplier;

echo "=== Creating Inventory Suppliers ===\n\n";

// Create inventory suppliers
$suppliers = [
    [
        'name' => 'Default Inventory Supplier',
        'code' => 'INV001',
        'contact_person' => 'John Doe',
        'email' => 'default@inventory.com',
        'phone' => '1234567890',
        'address' => '123 Supplier Street, City',
        'is_active' => true
    ],
    [
        'name' => 'Fresh Food Supplier',
        'code' => 'INV002',
        'contact_person' => 'Jane Smith',
        'email' => 'fresh@supplier.com',
        'phone' => '2345678901',
        'address' => '456 Fresh Avenue, City',
        'is_active' => true
    ],
    [
        'name' => 'Dry Goods Supplier',
        'code' => 'INV003',
        'contact_person' => 'Mike Johnson',
        'email' => 'dry@supplier.com',
        'phone' => '3456789012',
        'address' => '789 Dry Goods Road, City',
        'is_active' => true
    ]
];

foreach ($suppliers as $supplierData) {
    $supplier = InventorySupplier::firstOrCreate(
        ['code' => $supplierData['code']],
        $supplierData
    );
    
    if ($supplier->wasRecentlyCreated) {
        echo "✓ Created: {$supplier->name}\n";
    } else {
        echo "- Already exists: {$supplier->name}\n";
    }
}

echo "\n=== Inventory Suppliers Created ===\n";
echo "Total inventory suppliers: " . InventorySupplier::count() . "\n"; 