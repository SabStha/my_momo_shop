<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Merchandise;
use Illuminate\Support\Facades\DB;

echo "=== Debugging Merchandise Query ===\n\n";

echo "1. Direct DB Query (all merchandises):\n";
$allMerch = DB::table('merchandises')->get();
echo "   Total: " . $allMerch->count() . "\n";
foreach ($allMerch as $item) {
    $purchasable = $item->purchasable ? '💰' : '🏆';
    echo "   $purchasable {$item->name} (active: {$item->is_active}, category: {$item->category}, model: {$item->model})\n";
}

echo "\n2. Using Merchandise::all():\n";
$allModels = Merchandise::all();
echo "   Total: " . $allModels->count() . "\n";
foreach ($allModels as $item) {
    $purchasable = $item->purchasable ? '💰' : '🏆';
    echo "   $purchasable {$item->name}\n";
}

echo "\n3. Using Merchandise::active():\n";
$activeModels = Merchandise::active()->get();
echo "   Total: " . $activeModels->count() . "\n";
foreach ($activeModels as $item) {
    $purchasable = $item->purchasable ? '💰' : '🏆';
    echo "   $purchasable {$item->name}\n";
}

echo "\n4. Using Merchandise::active()->byCategory('accessories'):\n";
$accessories = Merchandise::active()->byCategory('accessories')->get();
echo "   Total: " . $accessories->count() . "\n";
foreach ($accessories as $item) {
    $purchasable = $item->purchasable ? '💰' : '🏆';
    echo "   $purchasable {$item->name} (model: {$item->model})\n";
}

echo "\n5. Using Merchandise::active()->byCategory('accessories')->byModel('all'):\n";
$accessoriesAll = Merchandise::active()->byCategory('accessories')->byModel('all')->get();
echo "   Total: " . $accessoriesAll->count() . "\n";
foreach ($accessoriesAll as $item) {
    $purchasable = $item->purchasable ? '💰' : '🏆';
    echo "   $purchasable {$item->name}\n";
}

echo "\n6. Using Merchandise::active()->byCategory('toys'):\n";
$toys = Merchandise::active()->byCategory('toys')->get();
echo "   Total: " . $toys->count() . "\n";
foreach ($toys as $item) {
    $purchasable = $item->purchasable ? '💰' : '🏆';
    echo "   $purchasable {$item->name} (model: {$item->model})\n";
}

echo "\n=== Debug Complete ===\n";

