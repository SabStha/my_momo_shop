<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n========================================\n";
echo "FIX MIGRATION ISSUE\n";
echo "========================================\n\n";

$migration = '2025_09_14_060618_create_bulk_settings_table';

// Check if table exists
$tableExists = Schema::hasTable('bulk_settings');
echo "bulk_settings table exists: " . ($tableExists ? 'YES' : 'NO') . "\n";

// Check if migration is recorded
$migrationRecorded = DB::table('migrations')->where('migration', $migration)->exists();
echo "Migration recorded in database: " . ($migrationRecorded ? 'YES' : 'NO') . "\n\n";

if ($tableExists && !$migrationRecorded) {
    echo "FIXING: Table exists but migration not recorded.\n";
    echo "Marking migration as run...\n";
    
    DB::table('migrations')->insert([
        'migration' => $migration,
        'batch' => 1
    ]);
    
    echo "✅ Migration marked as run!\n";
} elseif ($tableExists && $migrationRecorded) {
    echo "✅ No action needed. Table exists and migration is recorded.\n";
} else {
    echo "⚠️ Unexpected state. Please run migrations manually.\n";
}

echo "\n========================================\n";
echo "Now you can run: php artisan migrate\n";
echo "========================================\n\n";

