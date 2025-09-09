<?php

/**
 * Manual migration script for devices table
 * Run this script directly: php scripts/run-devices-migration.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "Starting devices table migration...\n";

try {
    // Check if table already exists
    if (Schema::hasTable('devices')) {
        echo "Devices table already exists. Dropping it first...\n";
        Schema::dropIfExists('devices');
    }

    // Create the devices table
    Schema::create('devices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
        $table->string('token')->unique();
        $table->string('platform', 10);
        $table->timestamp('last_used_at')->nullable();
        $table->timestamps();
        
        // Index for faster lookups
        $table->index(['user_id', 'platform']);
    });

    echo "✅ Devices table created successfully!\n";
    
    // Verify the table was created
    if (Schema::hasTable('devices')) {
        echo "✅ Table verification: devices table exists\n";
        
        // Show table structure
        $columns = DB::select("DESCRIBE devices");
        echo "Table structure:\n";
        foreach ($columns as $column) {
            echo "  - {$column->Field}: {$column->Type}\n";
        }
    } else {
        echo "❌ Table verification failed: devices table not found\n";
    }

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\nMigration completed successfully!\n";
