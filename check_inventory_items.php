<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\InventoryItem;

echo "=== Inventory Items ===\n";

$items = InventoryItem::select('id', 'name')->get();

foreach ($items as $item) {
    echo "ID: {$item->id}, Name: {$item->name}\n";
}

echo "=== End ===\n"; 