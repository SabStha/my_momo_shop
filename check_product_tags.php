<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "🏷️ Product Tags Verification:\n\n";

echo "Momos by Tag:\n";
echo "  Buff: " . Product::where('category', 'momo')->where('tag', 'buff')->count() . " items\n";
echo "  Chicken: " . Product::where('category', 'momo')->where('tag', 'chicken')->count() . " items\n";
echo "  Veg: " . Product::where('category', 'momo')->where('tag', 'veg')->count() . " items\n\n";

echo "Sides:\n";
echo "  Others: " . Product::where('category', 'sides')->where('tag', 'others')->count() . " items\n\n";

echo "Drinks by Tag:\n";
echo "  Hot: " . Product::where('tag', 'hot')->count() . " items\n";
echo "  Cold: " . Product::where('tag', 'cold')->count() . " items\n";
echo "  Boba: " . Product::where('tag', 'boba')->count() . " items\n\n";

echo "Desserts:\n";
echo "  Desserts: " . Product::where('tag', 'desserts')->count() . " items\n\n";

echo "Combos:\n";
echo "  Combos: " . Product::where('tag', 'combos')->count() . " items\n\n";

echo "Sample products with tags:\n";
$samples = Product::take(10)->get(['name', 'category', 'tag']);
foreach ($samples as $p) {
    echo "  - {$p->name} | category: {$p->category} | tag: {$p->tag}\n";
}

echo "\n✅ Tag verification complete!\n";

