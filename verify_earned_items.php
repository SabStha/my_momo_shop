<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Ama's Finds - Earned Items Verification ===\n\n";

$earnedItems = DB::table('merchandises')
    ->where('purchasable', false)
    ->get();

if ($earnedItems->count() > 0) {
    echo "✅ Found " . $earnedItems->count() . " earned items:\n\n";
    
    foreach ($earnedItems as $item) {
        echo "📦 " . $item->name . "\n";
        echo "   ├─ Category: " . $item->category . "\n";
        echo "   ├─ Status: " . $item->status . "\n";
        echo "   ├─ Badge: " . $item->badge . "\n";
        echo "   ├─ Purchasable: " . ($item->purchasable ? 'Yes' : 'No ❌') . "\n";
        echo "   ├─ Price: Rs. " . $item->price . "\n";
        echo "   └─ Description: " . substr($item->description, 0, 80) . "...\n\n";
    }
} else {
    echo "❌ No earned items found in database\n";
}

echo "\n=== All Merchandise Items ===\n\n";

$allMerch = DB::table('merchandises')->get();
echo "Total merchandise items: " . $allMerch->count() . "\n\n";

foreach ($allMerch as $item) {
    $icon = $item->purchasable ? '💰' : '🏆';
    echo $icon . " " . $item->name . " - " . ($item->purchasable ? 'Purchasable' : 'Earned Only') . "\n";
}

echo "\n=== Verification Complete ===\n";

