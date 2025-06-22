<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Supplier;
use App\Models\Branch;

echo "=== Supplier Branch Assignment Test ===\n\n";

// Get all branches
$branches = Branch::all();
echo "Branches:\n";
foreach ($branches as $branch) {
    echo "- Branch {$branch->id}: {$branch->name} (Main: " . ($branch->is_main ? 'Yes' : 'No') . ")\n";
}

echo "\nSuppliers:\n";
$suppliers = Supplier::with('branch')->get();
foreach ($suppliers as $supplier) {
    echo "- {$supplier->name} (Branch: " . ($supplier->branch ? $supplier->branch->name : 'None') . ")\n";
}

echo "\n=== Testing Branch-Specific Supplier Views ===\n";

// Test viewing suppliers for each branch
foreach ($branches as $branch) {
    echo "\nViewing suppliers for Branch {$branch->id} ({$branch->name}):\n";
    
    $query = Supplier::withCount('items')->with('branch');
    
    if ($branch->is_main) {
        $query->where('branch_id', $branch->id);
        echo "- Showing suppliers assigned to this main branch\n";
    } else {
        $mainBranch = Branch::where('is_main', true)->first();
        if ($mainBranch) {
            $query->where('branch_id', $mainBranch->id);
            echo "- Showing suppliers from main branch (centralized)\n";
        }
    }
    
    $suppliers = $query->get();
    foreach ($suppliers as $supplier) {
        echo "  * {$supplier->name} (assigned to: " . ($supplier->branch ? $supplier->branch->name : 'None') . ")\n";
    }
}

echo "\n=== Test Complete ===\n"; 