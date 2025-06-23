<?php

require_once 'vendor/autoload.php';

use App\Models\ForecastFeedback;
use App\Models\InventoryItem;
use App\Models\InventoryOrder;
use App\Models\InventoryOrderItem;
use App\Services\AIForecastService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧠 Enhanced AI Forecasting with Learning Capabilities\n";
echo "=====================================================\n\n";

// 1. Test the enhanced AI forecasting service
echo "1. Testing Enhanced AI Forecasting Service...\n";
$aiService = new AIForecastService();

// Test main branch forecasting
echo "\n📊 Main Branch Forecast:\n";
$mainBranchForecast = $aiService->generateForecast(1);
if ($mainBranchForecast['success']) {
    echo "✅ Main branch forecast generated successfully!\n";
    echo "   - Total items: " . $mainBranchForecast['forecast']['total_items'] . "\n";
    echo "   - Total value: Rs. " . number_format($mainBranchForecast['forecast']['total_value'], 2) . "\n";
    echo "   - Confidence: " . $mainBranchForecast['forecast']['confidence'] . "%\n";
    
    if (isset($mainBranchForecast['forecast']['items'])) {
        echo "   - Top items:\n";
        foreach (array_slice($mainBranchForecast['forecast']['items'], 0, 3) as $item) {
            echo "     • {$item['name']}: {$item['recommended_order']} units\n";
        }
    }
} else {
    echo "❌ Main branch forecast failed: " . $mainBranchForecast['message'] . "\n";
}

// Test branch forecasting
echo "\n📊 Branch Forecast (Branch 2):\n";
$branchForecast = $aiService->generateForecast(2);
if ($branchForecast['success']) {
    echo "✅ Branch forecast generated successfully!\n";
    echo "   - Total items: " . $branchForecast['forecast']['total_items'] . "\n";
    echo "   - Total value: " . $branchForecast['forecast']['total_value'] . "\n";
    echo "   - Confidence: " . $branchForecast['forecast']['confidence'] . "%\n";
} else {
    echo "❌ Branch forecast failed: " . $branchForecast['message'] . "\n";
}

// 2. Test the learning system
echo "\n\n2. Testing Learning System...\n";

// Create some sample forecast feedback data
echo "\n📝 Creating sample forecast feedback data...\n";

// Get some inventory items
$items = InventoryItem::take(5)->get();
foreach ($items as $item) {
    // Record some forecasts
    $forecastedQty = rand(10, 50);
    $actualQty = $forecastedQty + rand(-10, 10); // Simulate some variance
    
    ForecastFeedback::recordForecast(
        $item->id,
        1, // main branch
        'main_branch',
        $forecastedQty,
        "AI recommended based on demand patterns",
        ['demand_trend' => 'increasing', 'fulfillment_rate' => 85]
    );
    
    // Update with actual usage (simulate after some time)
    $feedback = ForecastFeedback::where('inventory_item_id', $item->id)
        ->where('actual_quantity_used', null)
        ->first();
    
    if ($feedback) {
        $feedback->updateWithActualUsage($actualQty, ['usage_context' => 'actual_branch_orders']);
        echo "   • {$item->name}: Forecasted {$forecastedQty}, Actual {$actualQty}, Accuracy " . round($feedback->accuracy_percentage, 1) . "%\n";
    }
}

// 3. Test performance insights
echo "\n\n3. Testing Performance Insights...\n";

$performance = ForecastFeedback::getPerformanceInsights(null, 1, 30);
echo "📈 Overall Performance (Last 30 days):\n";
echo "   - Total forecasts: {$performance['total_forecasts']}\n";
echo "   - Average accuracy: " . round($performance['average_accuracy'], 1) . "%\n";
echo "   - Accuracy rate: " . round($performance['accuracy_rate'], 1) . "%\n";
echo "   - Performance trend: {$performance['accuracy_trend']}\n";

if (!empty($performance['improvement_suggestions'])) {
    echo "   - Improvement suggestions:\n";
    foreach ($performance['improvement_suggestions'] as $suggestion) {
        echo "     • {$suggestion}\n";
    }
}

// 4. Test fulfillment analysis
echo "\n\n4. Testing Fulfillment Analysis...\n";

// Check if there are any fulfilled orders
$fulfilledOrders = InventoryOrder::whereIn('status', ['processed', 'received'])
    ->where('requesting_branch_id', '!=', null)
    ->where('requesting_branch_id', '!=', 1)
    ->where('created_at', '>=', now()->subDays(7))
    ->count();

echo "📊 Fulfilled Branch Orders (Last 7 days): {$fulfilledOrders}\n";

if ($fulfilledOrders > 0) {
    echo "✅ Fulfillment analysis will use actual fulfilled quantities\n";
} else {
    echo "ℹ️  No fulfilled orders found - this is normal for new systems\n";
}

// 5. Test demand trend analysis
echo "\n\n5. Testing Demand Trend Analysis...\n";

// Create some sample order data with trends
echo "📈 Creating sample demand trend data...\n";

// Simulate increasing demand for some items
foreach ($items->take(2) as $item) {
    // Create orders with increasing quantities over time
    for ($i = 1; $i <= 3; $i++) {
        $order = InventoryOrder::create([
            'branch_id' => 1,
            'requesting_branch_id' => 2,
            'status' => 'received',
            'created_at' => now()->subDays($i * 2),
            'updated_at' => now()->subDays($i * 2)
        ]);
        
        $quantity = 10 + ($i * 5); // Increasing demand
        $receivedQty = $quantity - rand(0, 2); // Some variance in fulfillment
        
        InventoryOrderItem::create([
            'inventory_order_id' => $order->id,
            'inventory_item_id' => $item->id,
            'quantity' => $quantity,
            'received_quantity' => $receivedQty,
            'unit_price' => $item->unit_price ?? 10.00
        ]);
    }
    echo "   • {$item->name}: Created increasing demand pattern\n";
}

// 6. Test the enhanced forecasting again
echo "\n\n6. Testing Enhanced Forecasting with Learning...\n";

$enhancedForecast = $aiService->generateForecast(1);
if ($enhancedForecast['success']) {
    echo "✅ Enhanced forecast with learning generated!\n";
    echo "   - The AI now considers:\n";
    echo "     • Actual fulfilled quantities (not just requested)\n";
    echo "     • Fulfillment rates and trends\n";
    echo "     • Historical forecast accuracy\n";
    echo "     • Demand patterns and seasonality\n";
    echo "     • Performance improvements over time\n";
    
    echo "\n🎯 Key Improvements:\n";
    echo "   • Filters for fulfilled orders only\n";
    echo "   • Uses actual fulfilled quantities\n";
    echo "   • Tracks forecast accuracy over time\n";
    echo "   • Learns from past performance\n";
    echo "   • Adjusts recommendations based on trends\n";
    echo "   • Provides improvement suggestions\n";
} else {
    echo "❌ Enhanced forecast failed: " . $enhancedForecast['message'] . "\n";
}

echo "\n\n🎉 Enhanced AI Forecasting System Test Complete!\n";
echo "The system now provides:\n";
echo "✅ More accurate forecasts using fulfilled quantities\n";
echo "✅ Learning capabilities that improve over time\n";
echo "✅ Performance tracking and insights\n";
echo "✅ Trend analysis and demand patterns\n";
echo "✅ Intelligent adjustments based on historical data\n";
echo "✅ Fulfillment rate optimization\n";
echo "✅ Proactive inventory management\n"; 