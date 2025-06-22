<?php

require_once 'vendor/autoload.php';

use App\Services\StockCheckService;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\InventoryOrder;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "📊 Stock Check Service Test\n";
echo "==========================\n\n";

$stockCheckService = new StockCheckService();

// 1. DAILY STOCK CHECK
echo "1. DAILY STOCK CHECK\n";
echo "===================\n";
echo "Purpose: Critical items and immediate actions\n";
echo "Frequency: Every day\n";
echo "Focus: Out of stock, low stock, critical alerts\n\n";

$dailyCheck = $stockCheckService->performDailyCheck(1); // Main branch

if (isset($dailyCheck['error'])) {
    echo "❌ Daily check failed: " . $dailyCheck['error'] . "\n\n";
} else {
    echo "✅ Daily check completed successfully!\n\n";
    
    echo "📋 SUMMARY:\n";
    echo "   - Total items checked: " . $dailyCheck['summary']['total_items_checked'] . "\n";
    echo "   - Critical alerts: " . $dailyCheck['summary']['critical_alerts_count'] . "\n";
    echo "   - Out of stock items: " . $dailyCheck['summary']['out_of_stock_count'] . "\n";
    echo "   - Low stock items: " . $dailyCheck['summary']['low_stock_count'] . "\n";
    echo "   - Overall status: " . strtoupper($dailyCheck['summary']['overall_status']) . "\n";
    echo "   - Next check: " . $dailyCheck['summary']['next_check_recommended'] . "\n\n";
    
    if (!empty($dailyCheck['critical_alerts'])) {
        echo "🚨 CRITICAL ALERTS:\n";
        foreach ($dailyCheck['critical_alerts'] as $alert) {
            echo "   • {$alert['message']} (Severity: {$alert['severity']})\n";
        }
        echo "\n";
    }
    
    if (!empty($dailyCheck['out_of_stock_items'])) {
        echo "❌ OUT OF STOCK ITEMS:\n";
        $outOfStockItems = $dailyCheck['out_of_stock_items']->toArray();
        foreach (array_slice($outOfStockItems, 0, 5) as $item) {
            echo "   • {$item['name']} (Category: {$item['category']})\n";
        }
        echo "\n";
    }
    
    if (!empty($dailyCheck['low_stock_items'])) {
        echo "⚠️ LOW STOCK ITEMS:\n";
        $lowStockItems = $dailyCheck['low_stock_items']->toArray();
        foreach (array_slice($lowStockItems, 0, 5) as $item) {
            echo "   • {$item['name']}: {$item['current_stock']} units ({$item['stock_level_percentage']}% of reorder point)\n";
        }
        echo "\n";
    }
    
    if (!empty($dailyCheck['recommendations'])) {
        echo "💡 DAILY RECOMMENDATIONS:\n";
        foreach ($dailyCheck['recommendations'] as $rec) {
            echo "   • {$rec['message']} (Priority: {$rec['priority']})\n";
            foreach ($rec['actions'] as $action) {
                echo "     - {$action}\n";
            }
        }
        echo "\n";
    }
}

// 2. WEEKLY STOCK CHECK
echo "2. WEEKLY STOCK CHECK\n";
echo "====================\n";
echo "Purpose: Trend analysis and planning\n";
echo "Frequency: Every week\n";
echo "Focus: Usage trends, supplier performance, cost analysis\n\n";

$weeklyCheck = $stockCheckService->performWeeklyCheck(1); // Main branch

if (isset($weeklyCheck['error'])) {
    echo "❌ Weekly check failed: " . $weeklyCheck['error'] . "\n\n";
} else {
    echo "✅ Weekly check completed successfully!\n\n";
    
    echo "📋 SUMMARY:\n";
    echo "   - Trends analyzed: " . $weeklyCheck['summary']['trends_analyzed'] . "\n";
    echo "   - Suppliers evaluated: " . $weeklyCheck['summary']['suppliers_evaluated'] . "\n";
    echo "   - Total weekly cost: Rs. " . number_format($weeklyCheck['summary']['total_weekly_cost'], 2) . "\n";
    echo "   - Recommendations: " . $weeklyCheck['summary']['recommendations_count'] . "\n";
    echo "   - Overall status: " . strtoupper($weeklyCheck['summary']['overall_status']) . "\n";
    echo "   - Next check: " . $weeklyCheck['summary']['next_check_recommended'] . "\n\n";
    
    if (!empty($weeklyCheck['trend_analysis'])) {
        echo "📈 WEEKLY TRENDS:\n";
        foreach (array_slice($weeklyCheck['trend_analysis'], 0, 5) as $trend) {
            echo "   • {$trend['item_name']}: Used {$trend['weekly_usage']} units, Purchased {$trend['weekly_purchases']} units\n";
        }
        echo "\n";
    }
    
    if (!empty($weeklyCheck['supplier_performance'])) {
        echo "🏢 SUPPLIER PERFORMANCE:\n";
        foreach ($weeklyCheck['supplier_performance'] as $supplier) {
            echo "   • {$supplier['supplier_name']}: {$supplier['fulfillment_rate']}% fulfillment rate ({$supplier['total_orders']} orders)\n";
        }
        echo "\n";
    }
    
    if (!empty($weeklyCheck['cost_analysis'])) {
        echo "💰 COST ANALYSIS:\n";
        $cost = $weeklyCheck['cost_analysis'];
        echo "   • Total purchases: Rs. " . number_format($cost['total_purchases'], 2) . "\n";
        echo "   • Total sales: Rs. " . number_format($cost['total_sales'], 2) . "\n";
        echo "   • Total waste: Rs. " . number_format($cost['total_waste'], 2) . "\n";
        echo "   • Waste percentage: " . round($cost['waste_percentage'], 1) . "%\n";
        echo "   • Net cost: Rs. " . number_format($cost['net_cost'], 2) . "\n\n";
    }
    
    if (!empty($weeklyCheck['recommendations'])) {
        echo "💡 WEEKLY RECOMMENDATIONS:\n";
        foreach ($weeklyCheck['recommendations'] as $rec) {
            echo "   • {$rec['message']} (Priority: {$rec['priority']})\n";
            foreach ($rec['actions'] as $action) {
                echo "     - {$action}\n";
            }
        }
        echo "\n";
    }
}

// 3. MONTHLY STOCK CHECK
echo "3. MONTHLY STOCK CHECK\n";
echo "=====================\n";
echo "Purpose: Strategic analysis and planning\n";
echo "Frequency: Every month\n";
echo "Focus: Profitability, optimization opportunities, strategic insights\n\n";

$monthlyCheck = $stockCheckService->performMonthlyCheck(1); // Main branch

if (isset($monthlyCheck['error'])) {
    echo "❌ Monthly check failed: " . $monthlyCheck['error'] . "\n\n";
} else {
    echo "✅ Monthly check completed successfully!\n\n";
    
    echo "📋 SUMMARY:\n";
    echo "   - Strategic insights: " . $monthlyCheck['summary']['strategic_insights'] . "\n";
    echo "   - Profitability items: " . $monthlyCheck['summary']['profitability_items'] . "\n";
    echo "   - Optimization opportunities: " . $monthlyCheck['summary']['optimization_opportunities'] . "\n";
    echo "   - Recommendations: " . $monthlyCheck['summary']['recommendations_count'] . "\n";
    echo "   - Overall status: " . strtoupper($monthlyCheck['summary']['overall_status']) . "\n";
    echo "   - Next check: " . $monthlyCheck['summary']['next_check_recommended'] . "\n\n";
    
    if (!empty($monthlyCheck['strategic_analysis'])) {
        echo "📊 STRATEGIC ANALYSIS:\n";
        $strategy = $monthlyCheck['strategic_analysis'];
        echo "   • Monthly sales: Rs. " . number_format($strategy['monthly_sales'], 2) . "\n";
        echo "   • Monthly purchases: Rs. " . number_format($strategy['monthly_purchases'], 2) . "\n";
        echo "   • Monthly waste: Rs. " . number_format($strategy['monthly_waste'], 2) . "\n";
        echo "   • Gross margin: Rs. " . number_format($strategy['gross_margin'], 2) . "\n";
        echo "   • Waste percentage: " . round($strategy['waste_percentage'], 1) . "%\n\n";
    }
    
    if (!empty($monthlyCheck['profitability_analysis'])) {
        echo "💰 TOP PROFITABLE ITEMS:\n";
        foreach (array_slice($monthlyCheck['profitability_analysis'], 0, 5) as $item) {
            echo "   • {$item['item_name']}: Rs. " . number_format($item['profitability'], 2) . " profit ({$item['profit_margin']}% margin)\n";
        }
        echo "\n";
    }
    
    if (!empty($monthlyCheck['optimization_opportunities'])) {
        echo "🎯 OPTIMIZATION OPPORTUNITIES:\n";
        foreach (array_slice($monthlyCheck['optimization_opportunities'], 0, 5) as $opp) {
            echo "   • {$opp['item_name']}: {$opp['type']} - {$opp['recommendation']}\n";
        }
        echo "\n";
    }
    
    if (!empty($monthlyCheck['recommendations'])) {
        echo "💡 MONTHLY RECOMMENDATIONS:\n";
        foreach ($monthlyCheck['recommendations'] as $rec) {
            echo "   • {$rec['message']} (Priority: {$rec['priority']})\n";
            foreach ($rec['actions'] as $action) {
                echo "     - {$action}\n";
            }
        }
        echo "\n";
    }
}

// 4. BRANCH-SPECIFIC CHECK
echo "4. BRANCH-SPECIFIC CHECK (Branch 2)\n";
echo "==================================\n";

$branchCheck = $stockCheckService->performDailyCheck(2); // Branch 2

if (isset($branchCheck['error'])) {
    echo "❌ Branch check failed: " . $branchCheck['error'] . "\n\n";
} else {
    echo "✅ Branch check completed successfully!\n\n";
    
    echo "📋 BRANCH SUMMARY:\n";
    echo "   - Critical alerts: " . $branchCheck['summary']['critical_alerts_count'] . "\n";
    echo "   - Out of stock items: " . $branchCheck['summary']['out_of_stock_count'] . "\n";
    echo "   - Low stock items: " . $branchCheck['summary']['low_stock_count'] . "\n";
    echo "   - Overall status: " . strtoupper($branchCheck['summary']['overall_status']) . "\n\n";
}

echo "🎉 Stock Check Service Test Complete!\n\n";

echo "📋 SUMMARY OF STOCK CHECK TYPES:\n";
echo "===============================\n\n";

echo "📅 DAILY CHECKS:\n";
echo "   • Critical alerts and immediate actions\n";
echo "   • Out of stock items\n";
echo "   • Low stock items (below reorder point)\n";
echo "   • Immediate recommendations for ordering\n";
echo "   • Focus: Operational urgency\n\n";

echo "📊 WEEKLY CHECKS:\n";
echo "   • Usage trend analysis\n";
echo "   • Supplier performance evaluation\n";
echo "   • Cost analysis and waste tracking\n";
echo "   • Weekly recommendations for improvement\n";
echo "   • Focus: Performance monitoring\n\n";

echo "📈 MONTHLY CHECKS:\n";
echo "   • Strategic analysis and profitability\n";
echo "   • Optimization opportunities\n";
echo "   • Long-term planning recommendations\n";
echo "   • Business intelligence insights\n";
echo "   • Focus: Strategic planning\n\n";

echo "🚀 BENEFITS:\n";
echo "   • Proactive inventory management\n";
echo "   • Reduced stockouts and overstocking\n";
echo "   • Improved supplier relationships\n";
echo "   • Cost optimization and waste reduction\n";
echo "   • Data-driven decision making\n";
echo "   • Automated monitoring and alerts\n"; 