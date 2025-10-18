<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\User;

echo "=== Database Stats Check ===\n\n";

try {
    $totalOrders = Order::count();
    echo "📦 Total Orders: " . $totalOrders . "\n";
    
    if ($totalOrders > 0) {
        $deliveredOrders = Order::where('status', 'delivered')->count();
        echo "   └─ Delivered: " . $deliveredOrders . "\n";
        
        $recentOrder = Order::orderBy('created_at', 'desc')->first();
        echo "   └─ Latest: " . $recentOrder->order_number . " (" . $recentOrder->status . ")\n";
    }
} catch (\Exception $e) {
    echo "❌ Error fetching orders: " . $e->getMessage() . "\n";
}

echo "\n";

try {
    $totalUsers = User::count();
    echo "👥 Total Users: " . $totalUsers . "\n";
    
    if ($totalUsers > 0) {
        $customers = User::where('role', 'customer')->count();
        echo "   └─ Customers: " . $customers . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error fetching users: " . $e->getMessage() . "\n";
}

echo "\n";

try {
    $totalReviews = DB::table('reviews')->count();
    echo "⭐ Total Reviews: " . $totalReviews . "\n";
    
    if ($totalReviews > 0) {
        $approvedReviews = DB::table('reviews')->where('is_approved', true)->count();
        echo "   └─ Approved: " . $approvedReviews . "\n";
        
        $featuredReviews = DB::table('reviews')->where('is_featured', true)->count();
        echo "   └─ Featured: " . $featuredReviews . "\n";
        
        $recentReviews = DB::table('reviews')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get(['customer_name', 'rating', 'product_name', 'created_at']);
        
        echo "   └─ Recent reviews:\n";
        foreach ($recentReviews as $review) {
            echo "      • " . $review->customer_name . " - " . $review->rating . "★ - " . $review->product_name . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error fetching reviews: " . $e->getMessage() . "\n";
}

echo "\n=== API Endpoints Check ===\n\n";

// Test the actual API endpoints
echo "Testing /api/home/benefits...\n";
try {
    $response = file_get_contents('http://localhost:8000/api/home/benefits');
    $data = json_decode($response, true);
    if (isset($data['data']['stats'])) {
        foreach ($data['data']['stats'] as $stat) {
            echo "   • " . $stat['label'] . ": " . $stat['value'] . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
}

echo "\nTesting /api/reviews...\n";
try {
    $response = file_get_contents('http://localhost:8000/api/reviews?featured=true');
    $data = json_decode($response, true);
    echo "   • Reviews count: " . ($data['count'] ?? 0) . "\n";
    echo "   • Reviews data: " . count($data['data'] ?? []) . " items\n";
} catch (\Exception $e) {
    echo "❌ API Error: " . $e->getMessage() . "\n";
}

echo "\n=== Check Complete ===\n";

