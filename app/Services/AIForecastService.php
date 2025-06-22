<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Branch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\InventoryOrder;

class AIForecastService
{
    protected $openaiApiKey;
    protected $openaiEndpoint = 'https://api.openai.com/v1/chat/completions';

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
    }

    public function generateForecast($branchId = null)
    {
        try {
            // Determine forecast type based on branch
            $isMainBranch = $branchId == 1 || $branchId === null;
            $forecastType = $isMainBranch ? 'main_branch' : 'branch';
            
            // Collect data for AI analysis
            $data = $this->collectForecastData($branchId, $forecastType);
            
            // Add historical performance data for learning
            $data['historical_performance'] = $this->getHistoricalPerformance($branchId, $forecastType);
            
            // Generate AI prompt based on forecast type
            $prompt = $this->buildAIPrompt($data, $forecastType);
            
            // Call OpenAI API
            $response = $this->callOpenAI($prompt);
            
            // Parse AI response
            $forecast = $this->parseAIResponse($response, $data);
            
            // Add forecast type to response
            $forecast['forecast_type'] = $forecastType;
            $forecast['is_main_branch'] = $isMainBranch;
            
            // Record forecasts for learning
            $this->recordForecasts($forecast, $branchId, $forecastType, $data);
            
            return [
                'success' => true,
                'forecast' => $forecast
            ];
            
        } catch (\Exception $e) {
            Log::error('AI Forecast Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to generate forecast: ' . $e->getMessage()
            ];
        }
    }

    protected function collectForecastData($branchId, $forecastType)
    {
        // Get current inventory based on forecast type
        $inventoryQuery = InventoryItem::with(['category', 'supplier']);
        
        if ($forecastType === 'main_branch') {
            // Main branch: Get inventory from all branches for aggregation
            $inventoryQuery->whereNotNull('branch_id');
        } else {
            // Other branches: Get only that branch's inventory
            $inventoryQuery->where('branch_id', $branchId);
        }
        
        $inventory = $inventoryQuery->get();

        // Get recent sales data (last 30 days)
        $salesData = Order::where('created_at', '>=', now()->subDays(30))
            ->with('items');
            
        if ($forecastType !== 'main_branch') {
            // For branches, only get their sales data
            $salesData->where('branch_id', $branchId);
        }
        
        $salesData = $salesData->get();

        // Get branch information
        $branches = Branch::all();
        
        // Get main branch info
        $mainBranch = Branch::where('is_main', true)->first();

        // Calculate low stock items
        $lowStockItems = $inventory->filter(function ($item) {
            return $item->current_stock <= $item->reorder_point;
        });

        // For main branch forecast, analyze branch orders
        $branchOrderAnalysis = null;
        if ($forecastType === 'main_branch') {
            $branchOrderAnalysis = $this->analyzeBranchOrders();
        }

        return [
            'inventory' => $inventory,
            'sales_data' => $salesData,
            'branches' => $branches,
            'main_branch' => $mainBranch,
            'low_stock_items' => $lowStockItems,
            'total_items' => $inventory->count(),
            'low_stock_count' => $lowStockItems->count(),
            'analysis_date' => now()->format('Y-m-d H:i:s'),
            'forecast_type' => $forecastType,
            'branch_id' => $branchId,
            'branch_order_analysis' => $branchOrderAnalysis
        ];
    }

    protected function analyzeBranchOrders()
    {
        // Get recent branch orders (last 7 days) - only fulfilled orders
        $recentBranchOrders = InventoryOrder::where('created_at', '>=', now()->subDays(7))
            ->where('requesting_branch_id', '!=', null) // Branch orders
            ->where('requesting_branch_id', '!=', 1) // Exclude main branch orders
            ->whereIn('status', ['processed', 'received']) // Only fulfilled orders
            ->with(['items.item', 'branch', 'requestingBranch'])
            ->get();

        // Analyze order patterns by item
        $orderAnalysis = [];
        
        foreach ($recentBranchOrders as $order) {
            foreach ($order->items as $orderItem) {
                $itemName = $orderItem->item->name;
                
                if (!isset($orderAnalysis[$itemName])) {
                    $orderAnalysis[$itemName] = [
                        'total_requested' => 0,
                        'total_fulfilled' => 0,
                        'order_count' => 0,
                        'branches_ordering' => [],
                        'avg_order_size' => 0,
                        'fulfillment_rate' => 0,
                        'last_ordered' => null,
                        'fulfillment_history' => []
                    ];
                }
                
                // Use actual fulfilled quantity (received_quantity) if available, otherwise use ordered quantity
                $fulfilledQty = $orderItem->received_quantity ?? $orderItem->quantity;
                
                $orderAnalysis[$itemName]['total_requested'] += $orderItem->quantity;
                $orderAnalysis[$itemName]['total_fulfilled'] += $fulfilledQty;
                $orderAnalysis[$itemName]['order_count']++;
                
                // Track fulfillment rate for this specific order
                $fulfillmentRate = $orderItem->quantity > 0 ? ($fulfilledQty / $orderItem->quantity) * 100 : 100;
                $orderAnalysis[$itemName]['fulfillment_history'][] = [
                    'requested' => $orderItem->quantity,
                    'fulfilled' => $fulfilledQty,
                    'rate' => $fulfillmentRate,
                    'date' => $order->created_at,
                    'branch' => $order->requestingBranch->name
                ];
                
                if (!in_array($order->requestingBranch->name, $orderAnalysis[$itemName]['branches_ordering'])) {
                    $orderAnalysis[$itemName]['branches_ordering'][] = $order->requestingBranch->name;
                }
                
                if (!$orderAnalysis[$itemName]['last_ordered'] || $order->created_at > $orderAnalysis[$itemName]['last_ordered']) {
                    $orderAnalysis[$itemName]['last_ordered'] = $order->created_at;
                }
            }
        }
        
        // Calculate averages and fulfillment rates
        foreach ($orderAnalysis as $itemName => &$analysis) {
            $analysis['avg_order_size'] = $analysis['order_count'] > 0 ? 
                $analysis['total_fulfilled'] / $analysis['order_count'] : 0;
            
            $analysis['fulfillment_rate'] = $analysis['total_requested'] > 0 ? 
                ($analysis['total_fulfilled'] / $analysis['total_requested']) * 100 : 100;
            
            // Calculate trend (increasing/decreasing demand)
            $analysis['demand_trend'] = $this->calculateDemandTrend($analysis['fulfillment_history']);
        }
        
        return [
            'recent_orders' => $recentBranchOrders,
            'order_analysis' => $orderAnalysis,
            'total_branch_orders' => $recentBranchOrders->count(),
            'analysis_period' => 'Last 7 days',
            'fulfillment_insights' => $this->generateFulfillmentInsights($orderAnalysis)
        ];
    }

    protected function calculateDemandTrend($fulfillmentHistory)
    {
        if (count($fulfillmentHistory) < 2) {
            return 'stable';
        }
        
        // Sort by date
        usort($fulfillmentHistory, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });
        
        // Calculate if demand is increasing or decreasing
        $firstHalf = array_slice($fulfillmentHistory, 0, ceil(count($fulfillmentHistory) / 2));
        $secondHalf = array_slice($fulfillmentHistory, ceil(count($fulfillmentHistory) / 2));
        
        $firstHalfAvg = array_sum(array_column($firstHalf, 'fulfilled')) / count($firstHalf);
        $secondHalfAvg = array_sum(array_column($secondHalf, 'fulfilled')) / count($secondHalf);
        
        $change = (($secondHalfAvg - $firstHalfAvg) / $firstHalfAvg) * 100;
        
        if ($change > 10) return 'increasing';
        if ($change < -10) return 'decreasing';
        return 'stable';
    }

    protected function generateFulfillmentInsights($orderAnalysis)
    {
        $insights = [
            'high_demand_items' => [],
            'low_fulfillment_items' => [],
            'trending_items' => [],
            'summary' => []
        ];
        
        foreach ($orderAnalysis as $itemName => $analysis) {
            // High demand items (ordered by multiple branches)
            if (count($analysis['branches_ordering']) >= 2) {
                $insights['high_demand_items'][] = [
                    'name' => $itemName,
                    'branches' => count($analysis['branches_ordering']),
                    'total_fulfilled' => $analysis['total_fulfilled']
                ];
            }
            
            // Low fulfillment items (less than 80% fulfillment rate)
            if ($analysis['fulfillment_rate'] < 80) {
                $insights['low_fulfillment_items'][] = [
                    'name' => $itemName,
                    'fulfillment_rate' => round($analysis['fulfillment_rate'], 1),
                    'requested' => $analysis['total_requested'],
                    'fulfilled' => $analysis['total_fulfilled']
                ];
            }
            
            // Trending items
            if ($analysis['demand_trend'] !== 'stable') {
                $insights['trending_items'][] = [
                    'name' => $itemName,
                    'trend' => $analysis['demand_trend'],
                    'avg_order_size' => round($analysis['avg_order_size'], 1)
                ];
            }
        }
        
        // Sort insights
        usort($insights['high_demand_items'], function($a, $b) {
            return $b['total_fulfilled'] <=> $a['total_fulfilled'];
        });
        
        usort($insights['low_fulfillment_items'], function($a, $b) {
            return $a['fulfillment_rate'] <=> $b['fulfillment_rate'];
        });
        
        // Generate summary
        $insights['summary'] = [
            'total_items_ordered' => count($orderAnalysis),
            'avg_fulfillment_rate' => count($orderAnalysis) > 0 ? 
                array_sum(array_column($orderAnalysis, 'fulfillment_rate')) / count($orderAnalysis) : 0,
            'high_demand_count' => count($insights['high_demand_items']),
            'low_fulfillment_count' => count($insights['low_fulfillment_items']),
            'trending_count' => count($insights['trending_items'])
        ];
        
        return $insights;
    }

    protected function buildAIPrompt($data, $forecastType)
    {
        if ($forecastType === 'main_branch') {
            return $this->buildMainBranchPrompt($data);
        } else {
            return $this->buildBranchPrompt($data);
        }
    }

    protected function buildMainBranchPrompt($data)
    {
        // Build inventory summary
        $inventorySummary = '';
        $inventory = $data['inventory']->take(20);
        foreach ($inventory as $item) {
            $inventorySummary .= "- {$item->name}: {$item->current_stock} units (Reorder: {$item->reorder_point})\n";
        }

        // Build low stock summary
        $lowStockSummary = '';
        $lowStockItems = $data['low_stock_items']->take(10);
        foreach ($lowStockItems as $item) {
            $lowStockSummary .= "- {$item->name}: {$item->current_stock} units (Reorder: {$item->reorder_point})\n";
        }

        // Build branch order analysis summary with enhanced insights
        $branchOrderSummary = '';
        if ($data['branch_order_analysis']) {
            $analysis = $data['branch_order_analysis'];
            $insights = $analysis['fulfillment_insights'];
            
            $branchOrderSummary = "\n\nBRANCH ORDER ANALYSIS ({$analysis['analysis_period']}):\n";
            $branchOrderSummary .= "- Total fulfilled branch orders: {$analysis['total_branch_orders']}\n";
            $branchOrderSummary .= "- Average fulfillment rate: " . round($insights['summary']['avg_fulfillment_rate'], 1) . "%\n";
            $branchOrderSummary .= "- Items with high demand (multiple branches): {$insights['summary']['high_demand_count']}\n";
            $branchOrderSummary .= "- Items with low fulfillment (<80%): {$insights['summary']['low_fulfillment_count']}\n";
            $branchOrderSummary .= "- Items with trending demand: {$insights['summary']['trending_count']}\n";
            
            if (!empty($analysis['order_analysis'])) {
                $branchOrderSummary .= "\nDETAILED ITEM ANALYSIS:\n";
                foreach (array_slice($analysis['order_analysis'], 0, 15, true) as $itemName => $itemAnalysis) {
                    $trendIcon = $itemAnalysis['demand_trend'] === 'increasing' ? '📈' : 
                                ($itemAnalysis['demand_trend'] === 'decreasing' ? '📉' : '➡️');
                    
                    $branchOrderSummary .= "  • {$itemName}:\n";
                    $branchOrderSummary .= "    - Fulfilled: {$itemAnalysis['total_fulfilled']} units (Requested: {$itemAnalysis['total_requested']})\n";
                    $branchOrderSummary .= "    - Fulfillment Rate: " . round($itemAnalysis['fulfillment_rate'], 1) . "%\n";
                    $branchOrderSummary .= "    - Branches: " . count($itemAnalysis['branches_ordering']) . " (" . implode(', ', $itemAnalysis['branches_ordering']) . ")\n";
                    $branchOrderSummary .= "    - Trend: {$trendIcon} {$itemAnalysis['demand_trend']}\n";
                    $branchOrderSummary .= "    - Avg Order Size: " . round($itemAnalysis['avg_order_size'], 1) . " units\n";
                }
            }
            
            // Add insights for low fulfillment items
            if (!empty($insights['low_fulfillment_items'])) {
                $branchOrderSummary .= "\n⚠️ LOW FULFILLMENT ITEMS (Need Attention):\n";
                foreach (array_slice($insights['low_fulfillment_items'], 0, 5) as $item) {
                    $branchOrderSummary .= "  • {$item['name']}: {$item['fulfillment_rate']}% fulfilled ({$item['fulfilled']}/{$item['requested']})\n";
                }
            }
            
            // Add trending items
            if (!empty($insights['trending_items'])) {
                $branchOrderSummary .= "\n📊 TRENDING ITEMS:\n";
                foreach (array_slice($insights['trending_items'], 0, 5) as $item) {
                    $trendIcon = $item['trend'] === 'increasing' ? '📈' : '📉';
                    $branchOrderSummary .= "  • {$item['name']}: {$trendIcon} {$item['trend']} demand (avg: {$item['avg_order_size']} units)\n";
                }
            }
        } else {
            $branchOrderSummary .= "- No recent fulfilled branch orders detected. This is normal for:\n";
            $branchOrderSummary .= "  • New system setup\n";
            $branchOrderSummary .= "  • New branches\n";
            $branchOrderSummary .= "  • New inventory items\n";
            $branchOrderSummary .= "  • Initial inventory establishment\n";
        }

        // Add historical performance data
        $historicalPerformance = '';
        if (isset($data['historical_performance'])) {
            $performance = $data['historical_performance']['overall_performance'];
            $historicalPerformance = "\n\nHISTORICAL FORECAST PERFORMANCE (Last 30 Days):\n";
            $historicalPerformance .= "- Total forecasts made: {$performance['total_forecasts']}\n";
            $historicalPerformance .= "- Average accuracy: " . round($performance['average_accuracy'], 1) . "%\n";
            $historicalPerformance .= "- Accuracy rate: " . round($performance['accuracy_rate'], 1) . "%\n";
            $historicalPerformance .= "- Performance trend: {$performance['accuracy_trend']}\n";
            
            if (!empty($performance['improvement_suggestions'])) {
                $historicalPerformance .= "\n📈 IMPROVEMENT SUGGESTIONS:\n";
                foreach ($performance['improvement_suggestions'] as $suggestion) {
                    $historicalPerformance .= "  • {$suggestion}\n";
                }
            }
            
            // Add item-specific performance for top items
            if (!empty($data['historical_performance']['item_performance'])) {
                $historicalPerformance .= "\n📊 TOP ITEMS PERFORMANCE:\n";
                foreach (array_slice($data['historical_performance']['item_performance'], 0, 5, true) as $itemName => $itemPerf) {
                    if ($itemPerf['total_forecasts'] > 0) {
                        $historicalPerformance .= "  • {$itemName}: " . round($itemPerf['average_accuracy'], 1) . "% accuracy ({$itemPerf['total_forecasts']} forecasts)\n";
                    }
                }
            }
        }

        return "You are an expert inventory management AI for a restaurant chain's MAIN BRANCH (Central Warehouse).

Current Situation:
- Total inventory items across all branches: {$data['total_items']}
- Low stock items: {$data['low_stock_count']}
- Analysis date: {$data['analysis_date']}
- Role: MAIN BRANCH (Central Warehouse) - Supplies all other branches

Current Inventory (Top 20 items):
{$inventorySummary}

Low Stock Items:
{$lowStockSummary}{$branchOrderSummary}{$historicalPerformance}

MAIN BRANCH FORECASTING RULES:
1. **SUPPLY CHAIN ROLE**: You are the central warehouse that supplies ALL branches
2. **PROACTIVE INVENTORY MANAGEMENT**: Create orders even without recent branch orders
3. **SAFETY STOCK MAINTENANCE**: Maintain adequate stock for branch operations
4. **EXTERNAL SUPPLIERS**: Order from external suppliers (not internal transfers)
5. **BULK ORDERING**: Order larger quantities for cost efficiency
6. **LEAD TIME CONSIDERATION**: Plan for longer lead times from suppliers
7. **FULFILLMENT LEARNING**: Use actual fulfilled quantities, not just requested
8. **TREND ANALYSIS**: Consider increasing/decreasing demand patterns
9. **FULFILLMENT RATE ADJUSTMENT**: Adjust for items with low fulfillment rates
10. **HISTORICAL LEARNING**: Learn from past forecast accuracy and adjust accordingly

DEMAND CALCULATION STRATEGY:
- **Actual Fulfilled Demand**: Use fulfilled quantities from branch orders (not requested)
- **Fulfillment Rate Adjustment**: For items with <80% fulfillment, consider increasing order size
- **Trend Analysis**: Increase orders for items with increasing demand trends
- **Historical Performance**: Adjust based on past forecast accuracy for each item
- **Current Inventory**: What's available across all branches
- **Low Stock Items**: Items that need immediate replenishment
- **Safety Stock**: Buffer for unexpected demand
- **Proactive Stocking**: Maintain inventory for future branch needs

ORDERING PRIORITIES:
1. **HIGH PRIORITY**: Items with zero or very low stock (out of stock items)
2. **MEDIUM PRIORITY**: Items below reorder points
3. **TRENDING PRIORITY**: Items with increasing demand trends
4. **LOW PRIORITY**: Items for proactive stocking and safety stock

FRESH vs. STORABLE STRATEGY:
1. **FRESH/PERISHABLE ITEMS** (Daily Delivery Required):
   - Order for 2-3 days coverage (supply multiple branches)
   - Examples: fresh vegetables, dairy, prepared foods, fresh meat, herbs
   - These must be delivered daily to branches

2. **NON-PERISHABLE/STORABLE ITEMS** (Multi-day Coverage):
   - Order for 5-7 days coverage (bulk ordering)
   - Examples: frozen items, canned goods, dry ingredients, rice, flour, cooking oil, spices
   - These can be stored longer and distributed to branches

INTELLIGENT ADJUSTMENTS:
- **For items with <80% fulfillment rate**: Increase order quantity by 20-30%
- **For items with increasing demand**: Increase order quantity by 15-25%
- **For items ordered by multiple branches**: Prioritize higher quantities
- **For items with stable demand**: Maintain current ordering levels
- **For items with decreasing demand**: Reduce order quantity by 10-15%
- **For items with poor historical accuracy**: Increase safety stock by 15-20%
- **For items with excellent historical accuracy**: Optimize quantities based on past performance

Task: Generate a MAIN BRANCH supply forecast to order from external suppliers. Focus on:
- Replenishing out-of-stock items (HIGHEST PRIORITY)
- Restocking items below reorder points
- Adjusting for fulfillment rates and demand trends
- Learning from historical forecast performance
- Maintaining safety stock for branch operations
- Proactive inventory management

Provide recommendations in this JSON format:
{
  \"items\": [
    {
      \"name\": \"Item Name\",
      \"id\": \"inventory_item_id\",
      \"current_stock\": 0,
      \"recommended_order\": 50,
      \"unit_price\": 10.50,
      \"item_type\": \"Fresh|Storable\",
      \"coverage_days\": 3,
      \"ordering_strategy\": \"Daily delivery required|Can be stored for multiple days\",
      \"reasoning\": \"Brief explanation of why this quantity was recommended\"
    }
  ],
  \"total_items\": 10,
  \"total_value\": 525.00,
  \"confidence\": 85
}";
    }

    protected function buildBranchPrompt($data)
    {
        $branchName = $data['branch_id'] ? "Branch ID: {$data['branch_id']}" : "This Branch";

        // Build inventory summary
        $inventorySummary = '';
        $inventory = $data['inventory']->take(20);
        foreach ($inventory as $item) {
            $inventorySummary .= "- {$item->name}: {$item->current_stock} units (Reorder: {$item->reorder_point})\n";
        }

        // Build low stock summary
        $lowStockSummary = '';
        $lowStockItems = $data['low_stock_items']->take(10);
        foreach ($lowStockItems as $item) {
            $lowStockSummary .= "- {$item->name}: {$item->current_stock} units (Reorder: {$item->reorder_point})\n";
        }

        return "You are an expert inventory management AI for a RESTAURANT BRANCH.

Current Situation:
- Total inventory items in this branch: {$data['total_items']}
- Low stock items: {$data['low_stock_count']}
- Analysis date: {$data['analysis_date']}
- Role: BRANCH - Orders from Main Branch (Central Warehouse)

Current Inventory (Top 20 items):
{$inventorySummary}

Low Stock Items:
{$lowStockSummary}

BRANCH FORECASTING RULES:
1. **SUPPLY CHAIN ROLE**: You are a branch that orders from the Main Branch (Central Warehouse)
2. **BRANCH-SPECIFIC DEMAND**: Calculate needs only for this specific branch
3. **INTERNAL TRANSFERS**: Order from Main Branch (not external suppliers)
4. **DAILY OPERATIONS**: Focus on daily operational needs
5. **SMALLER QUANTITIES**: Order smaller quantities for immediate use

FRESH vs. STORABLE STRATEGY:
1. **FRESH/PERISHABLE ITEMS** (Daily Delivery Required):
   - Order for 1 day coverage only
   - Examples: fresh vegetables, dairy, prepared foods, fresh meat, herbs
   - These must be delivered daily from Main Branch

2. **NON-PERISHABLE/STORABLE ITEMS** (Multi-day Coverage):
   - Order for 2-3 days coverage
   - Examples: frozen items, canned goods, dry ingredients, rice, flour, cooking oil, spices
   - These can be stored for a few days

Task: Generate a BRANCH supply forecast to order from the Main Branch (Central Warehouse). Focus on daily operational needs and smaller quantities.

Provide recommendations in this JSON format:
{
    \"total_items\": number,
    \"total_value\": \"Rs. X,XXX.XX\",
    \"confidence\": 85,
    \"forecast_type\": \"branch\",
    \"items\": [
        {
            \"name\": \"Item Name\",
            \"current_stock\": 5,
            \"recommended_order\": 20,
            \"unit_price\": \"25.00\",
            \"total_value\": \"500.00\",
            \"priority\": \"High|Medium|Low\",
            \"reasoning\": \"Brief explanation\",
            \"coverage_days\": 1,
            \"item_type\": \"Fresh|Storable\",
            \"ordering_strategy\": \"Daily delivery from Main Branch|Branch storage for 2-3 days\"
        }
    ]
}

Focus on daily operational needs and smaller quantities suitable for branch operations.";
    }

    protected function callOpenAI($prompt)
    {
        if (!$this->openaiApiKey) {
            // Fallback to mock data if no API key
            return $this->generateMockResponse();
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
            'Content-Type' => 'application/json',
        ])->post($this->openaiEndpoint, [
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an expert inventory management AI. Always respond with valid JSON.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3,
            'max_tokens' => 2000
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        // Fallback to mock data if API fails
        Log::warning('OpenAI API failed, using mock data');
        return $this->generateMockResponse();
    }

    protected function generateMockResponse()
    {
        // Determine if this is for main branch or regular branch
        $isMainBranch = true; // Default for mock data
        
        if ($isMainBranch) {
            return json_encode([
                'total_items' => 8,
                'total_value' => 'Rs. 45,750.00',
                'confidence' => 82,
                'forecast_type' => 'main_branch',
                'items' => [
                    [
                        'name' => 'Chicken Breast',
                        'current_stock' => 25,
                        'recommended_order' => 150,
                        'unit_price' => '250.00',
                        'total_value' => '37,500.00',
                        'priority' => 'High',
                        'reasoning' => 'Critical fresh ingredient, high branch demand observed',
                        'coverage_days' => 3,
                        'item_type' => 'Fresh',
                        'ordering_strategy' => 'Bulk order for multiple branches',
                        'branch_demand' => 'Based on recent branch orders: 85 units ordered by 3 branches'
                    ],
                    [
                        'name' => 'Rice (Basmati)',
                        'current_stock' => 50,
                        'recommended_order' => 200,
                        'unit_price' => '120.00',
                        'total_value' => '24,000.00',
                        'priority' => 'Medium',
                        'reasoning' => 'Staple item, consistent branch orders',
                        'coverage_days' => 7,
                        'item_type' => 'Storable',
                        'ordering_strategy' => 'Central warehouse storage',
                        'branch_demand' => 'Based on recent branch orders: 120 units ordered by 4 branches'
                    ],
                    [
                        'name' => 'Cooking Oil',
                        'current_stock' => 30,
                        'recommended_order' => 100,
                        'unit_price' => '12.50',
                        'total_value' => '1,250.00',
                        'priority' => 'Medium',
                        'reasoning' => 'Essential cooking ingredient, steady branch demand',
                        'coverage_days' => 7,
                        'item_type' => 'Storable',
                        'ordering_strategy' => 'Central warehouse storage',
                        'branch_demand' => 'Based on recent branch orders: 45 units ordered by 2 branches'
                    ],
                    [
                        'name' => 'Fresh Tomatoes',
                        'current_stock' => 15,
                        'recommended_order' => 80,
                        'unit_price' => '80.00',
                        'total_value' => '6,400.00',
                        'priority' => 'High',
                        'reasoning' => 'Fresh produce, daily branch orders',
                        'coverage_days' => 2,
                        'item_type' => 'Fresh',
                        'ordering_strategy' => 'Bulk order for multiple branches',
                        'branch_demand' => 'Based on recent branch orders: 60 units ordered by 3 branches'
                    ],
                    [
                        'name' => 'Frozen Peas',
                        'current_stock' => 40,
                        'recommended_order' => 120,
                        'unit_price' => '45.00',
                        'total_value' => '5,400.00',
                        'priority' => 'Low',
                        'reasoning' => 'Frozen item, moderate branch demand',
                        'coverage_days' => 7,
                        'item_type' => 'Storable',
                        'ordering_strategy' => 'Central warehouse storage',
                        'branch_demand' => 'Based on recent branch orders: 75 units ordered by 2 branches'
                    ]
                ]
            ]);
        } else {
            return json_encode([
                'total_items' => 6,
                'total_value' => 'Rs. 8,750.00',
                'confidence' => 78,
                'forecast_type' => 'branch',
                'items' => [
                    [
                        'name' => 'Chicken Breast',
                        'current_stock' => 5,
                        'recommended_order' => 25,
                        'unit_price' => '250.00',
                        'total_value' => '6,250.00',
                        'priority' => 'High',
                        'reasoning' => 'Critical fresh ingredient, daily needs',
                        'coverage_days' => 1,
                        'item_type' => 'Fresh',
                        'ordering_strategy' => 'Daily delivery from Main Branch'
                    ],
                    [
                        'name' => 'Rice (Basmati)',
                        'current_stock' => 8,
                        'recommended_order' => 30,
                        'unit_price' => '120.00',
                        'total_value' => '3,600.00',
                        'priority' => 'Medium',
                        'reasoning' => 'Staple item, 2-3 days coverage',
                        'coverage_days' => 2,
                        'item_type' => 'Storable',
                        'ordering_strategy' => 'Branch storage for 2-3 days'
                    ],
                    [
                        'name' => 'Cooking Oil',
                        'current_stock' => 3,
                        'recommended_order' => 15,
                        'unit_price' => '12.50',
                        'total_value' => '187.50',
                        'priority' => 'Medium',
                        'reasoning' => 'Essential cooking ingredient, small quantity',
                        'coverage_days' => 3,
                        'item_type' => 'Storable',
                        'ordering_strategy' => 'Branch storage for 2-3 days'
                    ],
                    [
                        'name' => 'Fresh Tomatoes',
                        'current_stock' => 2,
                        'recommended_order' => 10,
                        'unit_price' => '80.00',
                        'total_value' => '800.00',
                        'priority' => 'High',
                        'reasoning' => 'Fresh produce, daily delivery needed',
                        'coverage_days' => 1,
                        'item_type' => 'Fresh',
                        'ordering_strategy' => 'Daily delivery from Main Branch'
                    ],
                    [
                        'name' => 'Frozen Peas',
                        'current_stock' => 5,
                        'recommended_order' => 20,
                        'unit_price' => '45.00',
                        'total_value' => '900.00',
                        'priority' => 'Low',
                        'reasoning' => 'Frozen item, 2-3 days storage',
                        'coverage_days' => 2,
                        'item_type' => 'Storable',
                        'ordering_strategy' => 'Branch storage for 2-3 days'
                    ]
                ]
            ]);
        }
    }

    protected function parseAIResponse($response, $data)
    {
        try {
            // Try to parse JSON response
            $forecast = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If JSON parsing fails, extract JSON from text response
                preg_match('/\{.*\}/s', $response, $matches);
                if (!empty($matches[0])) {
                    $forecast = json_decode($matches[0], true);
                }
            }

            if (!$forecast) {
                throw new \Exception('Invalid AI response format');
            }

            // Log the parsed forecast for debugging
            Log::info('AI Forecast Response:', $forecast);
            
            // Ensure all items have the required fields and inject inventory item IDs
            if (isset($forecast['items']) && is_array($forecast['items'])) {
                // Build a map of inventory item names to IDs
                $inventoryMap = collect($data['inventory'] ?? [])->mapWithKeys(function($item) {
                    return [strtolower(trim($item->name)) => $item->id];
                });
                
                // Log the inventory map for debugging
                Log::info('Inventory Map:', $inventoryMap->toArray());
                
                $validItems = [];
                foreach ($forecast['items'] as $item) {
                    // Set default values if fields are missing
                    if (!isset($item['item_type'])) {
                        $item['item_type'] = 'Storable';
                    }
                    if (!isset($item['coverage_days'])) {
                        $item['coverage_days'] = 2;
                    }
                    if (!isset($item['ordering_strategy'])) {
                        $item['ordering_strategy'] = 'Can be stored for multiple days';
                    }
                    
                    // Inject inventory item ID if possible
                    if (!isset($item['id'])) {
                        $nameKey = strtolower(trim($item['name'] ?? ''));
                        if ($inventoryMap->has($nameKey)) {
                            $item['id'] = $inventoryMap[$nameKey];
                            Log::info("Matched item '{$item['name']}' to inventory ID: {$item['id']}");
                        } else {
                            Log::warning("No inventory item found for name: '{$item['name']}'");
                            // Skip items that don't have a corresponding inventory item
                            continue;
                        }
                    }
                    
                    $validItems[] = $item;
                }
                
                // Update the forecast with only valid items
                $forecast['items'] = $validItems;
                $forecast['total_items'] = count($validItems);
                
                Log::info('Processed forecast items:', $forecast['items']);
            }

            return $forecast;

        } catch (\Exception $e) {
            Log::error('Failed to parse AI response: ' . $e->getMessage());
            // Return mock data as fallback
            return json_decode($this->generateMockResponse(), true);
        }
    }

    protected function getHistoricalPerformance($branchId, $forecastType)
    {
        // Get performance insights for the last 30 days
        $performance = \App\Models\ForecastFeedback::getPerformanceInsights(null, $branchId, 30);
        
        // Get item-specific performance for top items
        $topItems = \App\Models\InventoryItem::when($branchId && !$this->isMainBranch($branchId), function($query) use ($branchId) {
            return $query->where('branch_id', $branchId);
        })->orderBy('current_stock', 'asc')->take(10)->get();
        
        $itemPerformance = [];
        foreach ($topItems as $item) {
            $itemPerformance[$item->name] = \App\Models\ForecastFeedback::getPerformanceInsights($item->id, $branchId, 30);
        }
        
        return [
            'overall_performance' => $performance,
            'item_performance' => $itemPerformance
        ];
    }

    protected function isMainBranch($branchId)
    {
        return $branchId == 1 || $branchId === null;
    }

    protected function recordForecasts($forecast, $branchId, $forecastType, $data)
    {
        if (!isset($forecast['items']) || !is_array($forecast['items'])) {
            return;
        }

        foreach ($forecast['items'] as $item) {
            if (!isset($item['id']) || !isset($item['recommended_order'])) {
                continue;
            }

            // Record the forecast
            \App\Models\ForecastFeedback::recordForecast(
                $item['id'],
                $branchId,
                $forecastType,
                $item['recommended_order'],
                $item['reasoning'] ?? null,
                [
                    'forecast_context' => $data,
                    'item_context' => $item
                ]
            );
        }
    }
} 