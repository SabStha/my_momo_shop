<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing /api/finds/data Endpoint ===\n\n";

try {
    $response = file_get_contents('http://localhost:8000/api/finds/data');
    $data = json_decode($response, true);
    
    echo "✅ API Response received\n\n";
    
    if (isset($data['merchandise'])) {
        echo "📦 Merchandise Categories:\n";
        foreach ($data['merchandise'] as $category => $items) {
            echo "   • $category: " . count($items) . " items\n";
            
            foreach ($items as $item) {
                $purchasable = $item['purchasable'] ? '💰' : '🏆';
                $badge = isset($item['badge']) ? " [{$item['badge']}]" : '';
                echo "      $purchasable {$item['name']} - Rs.{$item['price']}$badge\n";
            }
        }
    }
    
    echo "\n";
    
    if (isset($data['categories'])) {
        echo "🏷️ Categories:\n";
        foreach ($data['categories'] as $cat) {
            echo "   • {$cat['icon']} {$cat['label']} ({$cat['key']})\n";
        }
    }
    
    echo "\n";
    
    if (isset($data['bulkPackages'])) {
        echo "📦 Bulk Packages: " . count($data['bulkPackages']) . "\n";
    }
    
    echo "\n=== Earned Items Check ===\n\n";
    
    // Check specifically for earned items
    $hasEarnedItems = false;
    if (isset($data['merchandise'])) {
        foreach ($data['merchandise'] as $category => $items) {
            foreach ($items as $item) {
                if (!$item['purchasable']) {
                    $hasEarnedItems = true;
                    echo "🏆 Found earned item: {$item['name']}\n";
                    echo "   ├─ Category: $category\n";
                    echo "   ├─ Purchasable: " . ($item['purchasable'] ? 'Yes' : 'No ❌') . "\n";
                    echo "   └─ Badge: " . ($item['badge'] ?? 'None') . "\n\n";
                }
            }
        }
    }
    
    if (!$hasEarnedItems) {
        echo "❌ No earned items found in API response\n";
        echo "⚠️ Check if Merchandise model has 'active' scope that filters them out\n";
    }
    
} catch (\Exception $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";

