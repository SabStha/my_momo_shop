@echo off
echo Updating product tags...
echo.

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $u = DB::table('products')->where('category', 'momo')->where('name', 'like', '%%(Buff)%%')->update(['tag' => 'buff']); echo 'Buff momos: ' . $u . ' updated' . PHP_EOL;"

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $u = DB::table('products')->where('category', 'momo')->where('name', 'like', '%%(Chicken)%%')->update(['tag' => 'chicken']); echo 'Chicken momos: ' . $u . ' updated' . PHP_EOL;"

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $u = DB::table('products')->where('category', 'momo')->where('name', 'like', '%%(Veg)%%')->update(['tag' => 'veg']); echo 'Veg momos: ' . $u . ' updated' . PHP_EOL;"

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $u = DB::table('products')->where('category', 'sides')->update(['tag' => 'others']); echo 'Sides: ' . $u . ' updated' . PHP_EOL;"

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $u = DB::table('products')->where('category', 'hot-drinks')->update(['tag' => 'hot']); echo 'Hot drinks: ' . $u . ' updated' . PHP_EOL;"

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $u = DB::table('products')->where('category', 'cold-drinks')->where('name', '!=', 'Boba Drinks')->update(['tag' => 'cold']); echo 'Cold drinks: ' . $u . ' updated' . PHP_EOL;"

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $u = DB::table('products')->where('name', 'Boba Drinks')->update(['tag' => 'boba']); echo 'Boba drinks: ' . $u . ' updated' . PHP_EOL;"

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $u = DB::table('products')->where('category', 'desserts')->update(['tag' => 'desserts']); echo 'Desserts: ' . $u . ' updated' . PHP_EOL;"

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $u = DB::table('products')->where('category', 'combos')->update(['tag' => 'combos']); echo 'Combos: ' . $u . ' updated' . PHP_EOL;"

echo.
echo All tags updated!
echo.

php -r "require 'vendor/autoload.php'; $app = require 'bootstrap/app.php'; $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); use Illuminate\Support\Facades\DB; $tags = DB::table('products')->select('tag', DB::raw('COUNT(*) as count'))->groupBy('tag')->get(); echo 'Tag summary:' . PHP_EOL; foreach ($tags as $t) { echo '  ' . ($t->tag ?: 'NULL') . ': ' . $t->count . PHP_EOL; }"

pause

