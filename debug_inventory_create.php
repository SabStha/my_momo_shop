<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Category;

echo "=== Debug Inventory Create Logic ===\n\n";

// Simulate the request with branch=3
$branchId = 3;

echo "Requested Branch ID: {$branchId}\n";

// Find the branch
$branch = Branch::find($branchId);
if ($branch) {
    echo "Found Branch: {$branch->name} (ID: {$branch->id}, Main: " . ($branch->is_main ? 'Yes' : 'No') . ")\n";
} else {
    echo "Branch not found!\n";
    exit(1);
}

// Get main branch
$mainBranch = Branch::where('is_main', true)->first();
if ($mainBranch) {
    echo "Main Branch: {$mainBranch->name} (ID: {$mainBranch->id})\n";
} else {
    echo "No main branch found!\n";
    exit(1);
}

echo "\n=== Supplier Loading Logic ===\n";

// Test the old logic (all suppliers)
$allSuppliers = Supplier::orderBy('name')->get();
echo "All suppliers count: {$allSuppliers->count()}\n";
foreach ($allSuppliers as $supplier) {
    echo "- {$supplier->name} (Branch: " . ($supplier->branch ? $supplier->branch->name : 'None') . ")\n";
}

echo "\n=== New Logic (Main Branch Only) ===\n";

// Test the new logic (main branch suppliers only)
$mainBranchSuppliers = Supplier::where('branch_id', $mainBranch->id)->orderBy('name')->get();
echo "Main branch suppliers count: {$mainBranchSuppliers->count()}\n";
foreach ($mainBranchSuppliers as $supplier) {
    echo "- {$supplier->name} (Branch: " . ($supplier->branch ? $supplier->branch->name : 'None') . ")\n";
}

echo "\n=== Categories ===\n";
$categories = Category::orderBy('name')->get();
echo "Categories count: {$categories->count()}\n";

echo "\n=== Test Complete ===\n"; 