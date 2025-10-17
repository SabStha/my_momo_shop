<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;
use App\Models\Category;

echo "🍽️ Menu Seeder Results:\n\n";

$total = Product::count();
$momos = Product::where('category', 'momo')->count();
$sides = Product::where('category', 'sides')->count();
$hotDrinks = Product::where('category', 'hot-drinks')->count();
$coldDrinks = Product::where('category', 'cold-drinks')->count();
$desserts = Product::where('category', 'desserts')->count();
$combos = Product::where('category', 'combos')->count();

echo "Total Products Created: $total\n\n";

echo "By Category:\n";
echo "  Momos: $momos\n";
echo "  Sides: $sides\n";
echo "  Hot Drinks: $hotDrinks\n";
echo "  Cold Drinks: $coldDrinks\n";
echo "  Desserts: $desserts\n";
echo "  Combos: $combos\n\n";

if (Schema::hasTable('categories')) {
    $categories = Category::count();
    echo "Categories Created: $categories\n";
}

// Show sample products
echo "\nSample Products:\n";
$samples = Product::take(5)->get(['name', 'price', 'category']);
foreach ($samples as $p) {
    echo "  - {$p->name} (Rs {$p->price}) - {$p->category}\n";
}

echo "\n✅ Menu seeder ran successfully!\n";

