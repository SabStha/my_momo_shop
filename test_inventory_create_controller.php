<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Category;

echo "=== Testing Inventory Create Controller Logic ===\n\n";

// Simulate the exact controller logic
$branchId = 3; // South Branch
echo "Testing with branch ID: {$branchId}\n\n";

// Step 1: Find the branch
$branch = Branch::find($branchId);
if ($branch) {
    echo "Found Branch: {$branch->name} (ID: {$branch->id}, Main: " . ($branch->is_main ? 'Yes' : 'No') . ")\n";
} else {
    echo "Branch not found!\n";
    exit(1);
}

// Step 2: Get categories
$categories = Category::orderBy('name')->get();
echo "Categories count: {$categories->count()}\n";

// Step 3: Get suppliers (this is the key part we changed)
echo "\n=== Supplier Loading Logic ===\n";
$mainBranch = Branch::where('is_main', true)->first();
if ($mainBranch) {
    echo "Main Branch: {$mainBranch->name} (ID: {$mainBranch->id})\n";
    $suppliers = Supplier::where('branch_id', $mainBranch->id)->orderBy('name')->get();
    echo "Suppliers loaded: {$suppliers->count()}\n";
    
    foreach ($suppliers as $supplier) {
        echo "- {$supplier->name} (Branch: " . ($supplier->branch ? $supplier->branch->name : 'None') . ")\n";
    }
} else {
    echo "No main branch found!\n";
    $suppliers = collect();
}

// Step 4: Test the old logic for comparison
echo "\n=== Old Logic (All Suppliers) ===\n";
$allSuppliers = Supplier::orderBy('name')->get();
echo "All suppliers count: {$allSuppliers->count()}\n";
foreach ($allSuppliers as $supplier) {
    echo "- {$supplier->name} (Branch: " . ($supplier->branch ? $supplier->branch->name : 'None') . ")\n";
}

echo "\n=== Test Complete ===\n";
echo "The supplier dropdown should now only show: " . $suppliers->pluck('name')->implode(', ') . "\n"; 