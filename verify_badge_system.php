<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 Verifying Badge System on Production...\n\n";

// Direct DB queries to avoid cache
$badgeClasses = DB::table('badge_classes')->count();
$badgeRanks = DB::table('badge_ranks')->count();
$badgeTiers = DB::table('badge_tiers')->count();
$userBadges = DB::table('user_badges')->where('user_id', 1)->count();
$badgeProgress = DB::table('badge_progress')->where('user_id', 1)->count();

echo "📊 Badge System Tables:\n";
echo "  Badge Classes: {$badgeClasses}\n";
echo "  Badge Ranks: {$badgeRanks}\n";
echo "  Badge Tiers: {$badgeTiers}\n\n";

echo "👤 User ID 1 (Sabs):\n";
echo "  User Badges: {$userBadges}\n";
echo "  Badge Progress: {$badgeProgress}\n\n";

if ($badgeClasses > 0) {
    echo "✅ Badge classes exist!\n";
    $classes = DB::table('badge_classes')->get();
    foreach ($classes as $class) {
        echo "  • {$class->name} ({$class->code})\n";
    }
} else {
    echo "❌ No badge classes! Need to seed.\n";
}

echo "\n";

if ($userBadges > 0) {
    echo "✅ User has {$userBadges} badges!\n";
    $badges = DB::table('user_badges')
        ->join('badge_tiers', 'user_badges.badge_tier_id', '=', 'badge_tiers.id')
        ->join('badge_ranks', 'badge_tiers.badge_rank_id', '=', 'badge_ranks.id')
        ->where('user_badges.user_id', 1)
        ->select('badge_ranks.name as rank', 'badge_tiers.level as tier_level')
        ->limit(5)
        ->get();
    
    foreach ($badges as $badge) {
        echo "  • {$badge->rank} - Tier {$badge->tier_level}\n";
    }
} else {
    echo "⚠️ User has no badges yet. Need to run: php artisan badges:process 1\n";
}

echo "\n";

if ($badgeProgress > 0) {
    echo "✅ User has badge progress!\n";
    $progress = DB::table('badge_progress')
        ->join('badge_classes', 'badge_progress.badge_class_id', '=', 'badge_classes.id')
        ->where('badge_progress.user_id', 1)
        ->select('badge_classes.name', 'badge_progress.current_points')
        ->get();
    
    foreach ($progress as $p) {
        echo "  • {$p->name}: {$p->current_points} points\n";
    }
} else {
    echo "⚠️ User has no badge progress. Badge processing hasn't run yet.\n";
}

