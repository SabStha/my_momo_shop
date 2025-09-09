<?php

/**
 * Script to copy project images to the database products table
 * Run this script to populate product images from the existing project images
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

echo "🚀 Starting to copy product images to database...\n\n";

// Define image mappings for different food types
$imageMappings = [
    // Chicken momos
    'chicken' => [
        'steamed-chicken-momos.jpg',
        'spicy-chicken-momos.jpg', 
        'fried-chicken-momos.jpg',
        'tandoori-momos.jpg'
    ],
    // Vegetarian momos
    'veg' => [
        'veg-momos.jpg',
        'cheese-corn-momos.jpg'
    ],
    // Paneer momos
    'paneer' => [
        'Paneer-momos.jpg'
    ],
    // Pork momos
    'pork' => [
        'classic-pork-momos.jpg'
    ],
    // Spicy momos
    'chilli' => [
        'Chilli-garlic-momos.jpg'
    ]
];

// Get all products from database
$products = Product::all();
echo "📊 Found " . $products->count() . " products in database\n\n";

$updatedCount = 0;
$skippedCount = 0;

foreach ($products as $product) {
    $productName = strtolower($product->name);
    $assignedImage = null;
    
    // Try to find a matching image based on product name
    foreach ($imageMappings as $keyword => $images) {
        if (strpos($productName, $keyword) !== false) {
            // Pick a random image from the matching category
            $assignedImage = $images[array_rand($images)];
            break;
        }
    }
    
    // If no specific match, assign a default image
    if (!$assignedImage) {
        $defaultImages = [
            'steamed-chicken-momos.jpg',
            'veg-momos.jpg',
            'cheese-corn-momos.jpg'
        ];
        $assignedImage = $defaultImages[array_rand($defaultImages)];
    }
    
    // Check if product already has an image
    if ($product->image) {
        echo "⏭️  Product '{$product->name}' already has image: {$product->image}\n";
        $skippedCount++;
        continue;
    }
    
    // Update the product with the image path
    $imagePath = 'products/foods/' . $assignedImage;
    
    try {
        $product->update(['image' => $imagePath]);
        echo "✅ Updated '{$product->name}' with image: {$imagePath}\n";
        $updatedCount++;
    } catch (Exception $e) {
        echo "❌ Failed to update '{$product->name}': " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Image assignment completed!\n";
echo "📈 Updated: {$updatedCount} products\n";
echo "⏭️  Skipped: {$skippedCount} products (already had images)\n";
echo "📊 Total processed: " . ($updatedCount + $skippedCount) . " products\n";

// Show some examples of updated products
echo "\n📋 Sample updated products:\n";
$sampleProducts = Product::whereNotNull('image')->take(5)->get();
foreach ($sampleProducts as $product) {
    echo "  • {$product->name} → {$product->image}\n";
}

echo "\n✨ All done! Product images are now in the database.\n";
