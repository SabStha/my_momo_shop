<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Updating product tags...\n\n";

// Update momos
$updated = DB::table('products')
    ->where('category', 'momo')
    ->where('name', 'like', '%(Buff)%')
    ->update(['tag' => 'buff']);
echo "Buff momos updated: $updated\n";

$updated = DB::table('products')
    ->where('category', 'momo')
    ->where('name', 'like', '%(Chicken)%')
    ->update(['tag' => 'chicken']);
echo "Chicken momos updated: $updated\n";

$updated = DB::table('products')
    ->where('category', 'momo')
    ->where('name', 'like', '%(Veg)%')
    ->update(['tag' => 'veg']);
echo "Veg momos updated: $updated\n";

// Update sides
$updated = DB::table('products')
    ->where('category', 'sides')
    ->update(['tag' => 'others']);
echo "Sides updated: $updated\n";

// Update hot drinks
$updated = DB::table('products')
    ->where('category', 'hot-drinks')
    ->update(['tag' => 'hot']);
echo "Hot drinks updated: $updated\n";

// Update cold drinks (except boba)
$updated = DB::table('products')
    ->where('category', 'cold-drinks')
    ->where('name', '!=', 'Boba Drinks')
    ->update(['tag' => 'cold']);
echo "Cold drinks updated: $updated\n";

$updated = DB::table('products')
    ->where('category', 'cold-drinks')
    ->where('name', '=', 'Boba Drinks')
    ->update(['tag' => 'boba']);
echo "Boba drinks updated: $updated\n";

// Update desserts
$updated = DB::table('products')
    ->where('category', 'desserts')
    ->update(['tag' => 'desserts']);
echo "Desserts updated: $updated\n";

// Update combos
$updated = DB::table('products')
    ->where('category', 'combos')
    ->update(['tag' => 'combos']);
echo "Combos updated: $updated\n";

echo "\n✅ All tags updated!\n\n";

// Verify
echo "Verification:\n";
$tags = DB::table('products')
    ->select('tag', DB::raw('COUNT(*) as count'))
    ->groupBy('tag')
    ->get();

foreach ($tags as $tag) {
    echo "  {$tag->tag}: {$tag->count} products\n";
}

