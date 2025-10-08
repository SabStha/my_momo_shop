<?php

/**
 * Execute Full Database Cleanup
 * This script truncates all business data while preserving infrastructure
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "\n";
echo "========================================\n";
echo "FULL DATABASE CLEANUP\n";
echo "========================================\n\n";

// Disable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS = 0');
echo "✅ Foreign key checks disabled\n\n";

$tablesToTruncate = [
    // Business transactions
    'orders', 'order_items', 'payments', 'product_ratings',
    
    // Financial
    'wallets', 'wallet_transactions', 'ama_credits', 'ama_credit_transactions',
    'cashouts', 'payouts', 'payout_requests',
    
    // User progress
    'user_badges', 'badge_progress', 'user_task_completions', 'user_reward_redemptions',
    
    // Marketing
    'campaigns', 'campaign_triggers', 'coupons', 'coupon_usages', 'user_coupons',
    'offers', 'offer_claims', 'referrals', 'customer_segments',
    
    // Creator program
    'rewards', 'creator_earnings', 'creator_rewards',
    
    // Inventory
    'inventories', 'branch_inventory', 'inventory_transactions', 'stock_items',
    'supply_orders', 'supply_order_items', 'weekly_stock_checks', 'monthly_stock_checks',
    
    // POS
    'cash_drawers', 'cash_drawer_sessions', 'cash_drawer_logs', 'cash_drawer_adjustments',
    
    // Employee
    'employees', 'employee_schedules', 'time_logs', 'time_entries',
    
    // Analytics
    'activity_log', 'pos_access_logs', 'churn_predictions', 'forecast_feedback',
    'customer_feedback', 'investment_page_visits',
    
    // Investors
    'investors', 'investor_investments', 'investor_payouts', 'investor_reports',
    
    // Products (will re-seed)
    'products',
    
    // Other
    'merchandises', 'bulk_packages', 'user_themes',
    
    // Cache & sessions
    'cache', 'sessions', 'jobs', 'failed_jobs',
];

$truncated = 0;
$skipped = 0;
$errors = [];

foreach ($tablesToTruncate as $table) {
    try {
        if (Schema::hasTable($table)) {
            DB::table($table)->truncate();
            echo "✅ Truncated: $table\n";
            $truncated++;
        } else {
            echo "⚠️  Skipped (not exists): $table\n";
            $skipped++;
        }
    } catch (\Exception $e) {
        echo "❌ Error truncating $table: " . $e->getMessage() . "\n";
        $errors[] = $table;
    }
}

// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS = 1');
echo "\n✅ Foreign key checks re-enabled\n\n";

echo "========================================\n";
echo "CLEANUP SUMMARY\n";
echo "========================================\n";
echo "Truncated: $truncated tables\n";
echo "Skipped: $skipped tables\n";
echo "Errors: " . count($errors) . " tables\n";

if (!empty($errors)) {
    echo "\nTables with errors:\n";
    foreach ($errors as $table) {
        echo "  - $table\n";
    }
}

echo "\n";
echo "========================================\n";
echo "VERIFICATION\n";
echo "========================================\n\n";

// Verify key tables
$verification = [
    'users' => DB::table('users')->count(),
    'products' => DB::table('products')->count(),
    'orders' => DB::table('orders')->count(),
    'roles' => DB::table('roles')->count(),
    'permissions' => DB::table('permissions')->count(),
];

foreach ($verification as $table => $count) {
    echo "  $table: $count rows\n";
}

echo "\n";
echo "✅ Database cleanup complete!\n\n";

