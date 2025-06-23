<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Supplier;
use App\Models\Branch;

echo "=== Check All Suppliers ===\n\n";

// Get all branches
$branches = Branch::all();
echo "Branches:\n";
foreach ($branches as $branch) {
    echo "- Branch {$branch->id}: {$branch->name} (Main: " . ($branch->is_main ? 'Yes' : 'No') . ")\n";
}

echo "\nAll Suppliers:\n";
$allSuppliers = Supplier::with('branch')->get();
foreach ($allSuppliers as $supplier) {
    echo "- {$supplier->name} (Branch: " . ($supplier->branch ? $supplier->branch->name : 'None') . ", Branch ID: " . ($supplier->branch_id ?? 'NULL') . ")\n";
}

echo "\n=== Suppliers by Branch ===\n";
foreach ($branches as $branch) {
    $branchSuppliers = Supplier::where('branch_id', $branch->id)->get();
    echo "\nBranch {$branch->id} ({$branch->name}):\n";
    if ($branchSuppliers->count() > 0) {
        foreach ($branchSuppliers as $supplier) {
            echo "  - {$supplier->name}\n";
        }
    } else {
        echo "  - No suppliers\n";
    }
}

echo "\n=== Suppliers without Branch ===\n";
$suppliersWithoutBranch = Supplier::whereNull('branch_id')->get();
if ($suppliersWithoutBranch->count() > 0) {
    foreach ($suppliersWithoutBranch as $supplier) {
        echo "- {$supplier->name}\n";
    }
} else {
    echo "- No suppliers without branch assignment\n";
}

echo "\n=== Test Complete ===\n"; 