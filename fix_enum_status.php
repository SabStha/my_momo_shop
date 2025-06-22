<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Fixing ENUM status column in inventory_orders table...\n\n";

try {
    // First, let's see the current ENUM values
    echo "Current ENUM values:\n";
    $result = DB::select("SHOW COLUMNS FROM inventory_orders LIKE 'status'");
    if (!empty($result)) {
        echo "Type: " . $result[0]->Type . "\n";
    }
    
    echo "\nUpdating ENUM to include 'processed'...\n";
    
    // Update the ENUM to include 'processed'
    DB::statement("ALTER TABLE inventory_orders MODIFY COLUMN status ENUM('pending', 'sent', 'supplier_confirmed', 'processed', 'received', 'cancelled', 'rejected') NOT NULL DEFAULT 'pending'");
    
    echo "✅ ENUM updated successfully!\n\n";
    
    // Verify the update
    echo "Updated ENUM values:\n";
    $result = DB::select("SHOW COLUMNS FROM inventory_orders LIKE 'status'");
    if (!empty($result)) {
        echo "Type: " . $result[0]->Type . "\n";
    }
    
    echo "\n✅ Status column now includes 'processed'!\n";
    echo "You can now process branch orders without errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 