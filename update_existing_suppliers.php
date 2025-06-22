<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Supplier;
use App\Models\Branch;

echo "=== Updating Existing Suppliers ===\n\n";

// Get the main branch
$mainBranch = Branch::where('is_main', true)->first();

if (!$mainBranch) {
    echo "Error: No main branch found!\n";
    exit(1);
}

echo "Main branch found: {$mainBranch->name} (ID: {$mainBranch->id})\n\n";

// Get suppliers without branch assignment
$suppliersWithoutBranch = Supplier::whereNull('branch_id')->get();

echo "Found {$suppliersWithoutBranch->count()} suppliers without branch assignment:\n";

foreach ($suppliersWithoutBranch as $supplier) {
    echo "- {$supplier->name}\n";
}

// Update suppliers to main branch
$updatedCount = Supplier::whereNull('branch_id')->update(['branch_id' => $mainBranch->id]);

echo "\nUpdated {$updatedCount} suppliers to main branch.\n\n";

// Verify the update
$suppliers = Supplier::with('branch')->get();
echo "All suppliers after update:\n";
foreach ($suppliers as $supplier) {
    echo "- {$supplier->name} (Branch: " . ($supplier->branch ? $supplier->branch->name : 'None') . ")\n";
}

echo "\n=== Update Complete ===\n"; 