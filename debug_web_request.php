<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Category;
use Illuminate\Http\Request;

echo "=== Debug Web Request Simulation ===\n\n";

// Simulate the exact request
$request = new Request();
$request->query->set('branch', '3');

echo "Request URL: /admin/inventory/create?branch=3\n";
echo "Request Query Parameters: " . json_encode($request->query->all()) . "\n\n";

// Simulate the exact controller logic
$branchId = $request->query('branch');
echo "Branch ID from request: {$branchId}\n";

$branch = null;
if ($branchId) {
    $branch = Branch::findOrFail($branchId);
    echo "Found Branch: {$branch->name} (ID: {$branch->id}, Main: " . ($branch->is_main ? 'Yes' : 'No') . ")\n";
}

// Get categories
$categories = Category::orderBy('name')->get();
echo "Categories count: {$categories->count()}\n";

// Get suppliers (the key part)
echo "\n=== Supplier Loading ===\n";
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

// Also test what would happen with the old logic
echo "\n=== Old Logic (All Suppliers) ===\n";
$allSuppliers = Supplier::orderBy('name')->get();
echo "All suppliers count: {$allSuppliers->count()}\n";
foreach ($allSuppliers as $supplier) {
    echo "- {$supplier->name} (Branch: " . ($supplier->branch ? $supplier->branch->name : 'None') . ")\n";
}

echo "\n=== View Data ===\n";
echo "Suppliers passed to view: " . $suppliers->pluck('name')->implode(', ') . "\n";
echo "Categories passed to view: " . $categories->pluck('name')->implode(', ') . "\n";

echo "\n=== Test Complete ===\n"; 