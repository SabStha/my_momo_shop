<?php

require_once 'vendor/autoload.php';

use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\WeeklyStockCheck;
use App\Models\MonthlyStockCheck;

// Test the stock check functionality
echo "Testing Stock Check Functionality\n";
echo "================================\n\n";

// 1. Test getting inventory items
echo "1. Testing Inventory Items:\n";
$items = InventoryItem::with(['category', 'supplier'])->take(5)->get();
echo "Found " . $items->count() . " inventory items\n";
foreach ($items as $item) {
    echo "  - {$item->name} (SKU: {$item->code}, Stock: {$item->current_stock})\n";
}
echo "\n";

// 2. Test weekly stock checks
echo "2. Testing Weekly Stock Checks:\n";
$weeklyChecks = WeeklyStockCheck::with('inventoryItem')->take(3)->get();
echo "Found " . $weeklyChecks->count() . " weekly stock checks\n";
foreach ($weeklyChecks as $check) {
    echo "  - {$check->inventoryItem->name}: {$check->quantity_checked} (checked at {$check->checked_at})\n";
}
echo "\n";

// 3. Test monthly stock checks
echo "3. Testing Monthly Stock Checks:\n";
$monthlyChecks = MonthlyStockCheck::with('inventoryItem')->take(3)->get();
echo "Found " . $monthlyChecks->count() . " monthly stock checks\n";
foreach ($monthlyChecks as $check) {
    echo "  - {$check->inventoryItem->name}: {$check->quantity_checked} (checked at {$check->checked_at})\n";
}
echo "\n";

// 4. Test branches
echo "4. Testing Branches:\n";
$branches = Branch::all();
echo "Found " . $branches->count() . " branches\n";
foreach ($branches as $branch) {
    echo "  - {$branch->name} (ID: {$branch->id}, Main: " . ($branch->is_main ? 'Yes' : 'No') . ")\n";
}
echo "\n";

echo "Test completed successfully!\n";
echo "\nNext steps:\n";
echo "1. Add the missing buttons to the stock check dashboard\n";
echo "2. Create the weekly and monthly check views\n";
echo "3. Add the controller methods for weekly and monthly checks\n";
echo "4. Test the complete functionality\n"; 