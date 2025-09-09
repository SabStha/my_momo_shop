<?php

/**
 * Comprehensive script to set up products with images
 * Creates sample products if none exist, then assigns images
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Product;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

echo "🚀 Setting up products with images...\n\n";

// Check if we have any products
$productCount = Product::count();
echo "📊 Current products in database: {$productCount}\n";

// If no products exist, create some sample ones
if ($productCount === 0) {
    echo "\n📝 No products found. Creating sample products...\n";
    
    // Get or create a default branch
    $branch = Branch::first();
    if (!$branch) {
        $branch = Branch::create([
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'address' => '123 Main Street, Kathmandu',
            'is_active' => true,
            'is_main' => true
        ]);
        echo "🏢 Created default branch: {$branch->name}\n";
    }
    
    // Sample momo products
    $sampleProducts = [
        [
            'name' => 'Steamed Chicken Momos',
            'code' => 'SCM001',
            'description' => 'Delicious steamed chicken momos with herbs and spices',
            'price' => 180.00,
            'cost_price' => 120.00,
            'stock' => 100,
            'category' => 'Chicken',
            'tag' => 'chicken',
            'is_featured' => true,
            'is_active' => true,
            'branch_id' => $branch->id
        ],
        [
            'name' => 'Spicy Chicken Momos',
            'code' => 'SPCM002',
            'description' => 'Hot and spicy chicken momos with chili sauce',
            'price' => 200.00,
            'cost_price' => 130.00,
            'stock' => 80,
            'category' => 'Chicken',
            'tag' => 'chicken',
            'is_featured' => true,
            'is_active' => true,
            'branch_id' => $branch->id
        ],
        [
            'name' => 'Vegetable Momos',
            'code' => 'VM003',
            'description' => 'Fresh vegetable momos with mixed greens',
            'price' => 160.00,
            'cost_price' => 100.00,
            'stock' => 90,
            'category' => 'Vegetarian',
            'tag' => 'veg',
            'is_featured' => false,
            'is_active' => true,
            'branch_id' => $branch->id
        ],
        [
            'name' => 'Paneer Momos',
            'code' => 'PM004',
            'description' => 'Cottage cheese momos with Indian spices',
            'price' => 170.00,
            'cost_price' => 110.00,
            'stock' => 70,
            'category' => 'Vegetarian',
            'tag' => 'paneer',
            'is_featured' => false,
            'is_active' => true,
            'branch_id' => $branch->id
        ],
        [
            'name' => 'Cheese Corn Momos',
            'code' => 'CCM005',
            'description' => 'Cheesy corn momos with melted cheese',
            'price' => 190.00,
            'cost_price' => 125.00,
            'stock' => 60,
            'category' => 'Vegetarian',
            'tag' => 'veg',
            'is_featured' => false,
            'is_active' => true,
            'branch_id' => $branch->id
        ],
        [
            'name' => 'Fried Chicken Momos',
            'code' => 'FCM006',
            'description' => 'Crispy fried chicken momos with dipping sauce',
            'price' => 220.00,
            'cost_price' => 140.00,
            'stock' => 50,
            'category' => 'Chicken',
            'tag' => 'chicken',
            'is_featured' => false,
            'is_active' => true,
            'branch_id' => $branch->id
        ],
        [
            'name' => 'Tandoori Chicken Momos',
            'code' => 'TCM007',
            'description' => 'Tandoori spiced chicken momos with yogurt sauce',
            'price' => 210.00,
            'cost_price' => 135.00,
            'stock' => 45,
            'category' => 'Chicken',
            'tag' => 'chicken',
            'is_featured' => false,
            'is_active' => true,
            'branch_id' => $branch->id
        ],
        [
            'name' => 'Chilli Garlic Momos',
            'code' => 'CGM008',
            'description' => 'Spicy chilli garlic momos with hot sauce',
            'price' => 195.00,
            'cost_price' => 128.00,
            'stock' => 55,
            'category' => 'Spicy',
            'tag' => 'chilli',
            'is_featured' => false,
            'is_active' => true,
            'branch_id' => $branch->id
        ]
    ];
    
    foreach ($sampleProducts as $productData) {
        $product = Product::create($productData);
        echo "✅ Created product: {$product->name}\n";
    }
    
    echo "\n🎉 Created " . count($sampleProducts) . " sample products!\n";
}

// Now assign images to all products
echo "\n🖼️  Assigning images to products...\n";

// Define image mappings
$imageMappings = [
    'chicken' => [
        'steamed-chicken-momos.jpg',
        'spicy-chicken-momos.jpg', 
        'fried-chicken-momos.jpg',
        'tandoori-momos.jpg'
    ],
    'veg' => [
        'veg-momos.jpg',
        'cheese-corn-momos.jpg'
    ],
    'paneer' => [
        'Paneer-momos.jpg'
    ],
    'chilli' => [
        'Chilli-garlic-momos.jpg'
    ]
];

$products = Product::all();
$updatedCount = 0;
$skippedCount = 0;

foreach ($products as $product) {
    $productName = strtolower($product->name);
    $productTag = strtolower($product->tag ?? '');
    $assignedImage = null;
    
    // Try to find a matching image based on product tag first, then name
    if ($productTag && isset($imageMappings[$productTag])) {
        $assignedImage = $imageMappings[$productTag][array_rand($imageMappings[$productTag])];
    } else {
        // Try to find by name
        foreach ($imageMappings as $keyword => $images) {
            if (strpos($productName, $keyword) !== false) {
                $assignedImage = $images[array_rand($images)];
                break;
            }
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

// Show final results
echo "\n📋 Final product list with images:\n";
$finalProducts = Product::whereNotNull('image')->get();
foreach ($finalProducts as $product) {
    echo "  • {$product->name} → {$product->image}\n";
}

echo "\n✨ All done! Products now have images in the database.\n";
echo "💡 You can now use these images in your React Native app!\n";
