<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\InventoryTransaction;
use App\Models\InventoryOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class StockCheckService
{
    /**
     * DAILY STOCK CHECK - Critical items and immediate actions
     */
    public function performDailyCheck($branchId = null)
    {
        $results = [
            'check_type' => 'daily',
            'branch_id' => $branchId,
            'timestamp' => now(),
            'critical_alerts' => [],
            'out_of_stock_items' => [],
            'low_stock_items' => [],
            'recommendations' => [],
            'summary' => []
        ];

        try {
            // 1. CRITICAL ALERTS - Items that need immediate attention
            $results['critical_alerts'] = $this->getCriticalAlerts($branchId);
            
            // 2. OUT OF STOCK ITEMS - Zero stock items
            $results['out_of_stock_items'] = $this->getOutOfStockItems($branchId);
            
            // 3. LOW STOCK ITEMS - Below reorder point
            $results['low_stock_items'] = $this->getLowStockItems($branchId);
            
            // 4. DAILY RECOMMENDATIONS
            $results['recommendations'] = $this->getDailyRecommendations($results);
            
            // 5. SUMMARY
            $results['summary'] = $this->generateDailySummary($results);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error('Daily stock check failed: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'check_type' => 'daily'];
        }
    }

    /**
     * WEEKLY STOCK CHECK - Trend analysis and planning
     */
    public function performWeeklyCheck($branchId = null)
    {
        $results = [
            'check_type' => 'weekly',
            'branch_id' => $branchId,
            'timestamp' => now(),
            'trend_analysis' => [],
            'supplier_performance' => [],
            'cost_analysis' => [],
            'recommendations' => [],
            'summary' => []
        ];

        try {
            // 1. WEEKLY TREND ANALYSIS
            $results['trend_analysis'] = $this->getWeeklyTrends($branchId);
            
            // 2. SUPPLIER PERFORMANCE
            $results['supplier_performance'] = $this->getSupplierPerformance($branchId);
            
            // 3. COST ANALYSIS
            $results['cost_analysis'] = $this->getWeeklyCostAnalysis($branchId);
            
            // 4. WEEKLY RECOMMENDATIONS
            $results['recommendations'] = $this->getWeeklyRecommendations($results);
            
            // 5. SUMMARY
            $results['summary'] = $this->generateWeeklySummary($results);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error('Weekly stock check failed: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'check_type' => 'weekly'];
        }
    }

    /**
     * MONTHLY STOCK CHECK - Strategic analysis and planning
     */
    public function performMonthlyCheck($branchId = null)
    {
        $results = [
            'check_type' => 'monthly',
            'branch_id' => $branchId,
            'timestamp' => now(),
            'strategic_analysis' => [],
            'profitability_analysis' => [],
            'optimization_opportunities' => [],
            'recommendations' => [],
            'summary' => []
        ];

        try {
            // 1. STRATEGIC ANALYSIS
            $results['strategic_analysis'] = $this->getStrategicAnalysis($branchId);
            
            // 2. PROFITABILITY ANALYSIS
            $results['profitability_analysis'] = $this->getProfitabilityAnalysis($branchId);
            
            // 3. OPTIMIZATION OPPORTUNITIES
            $results['optimization_opportunities'] = $this->getOptimizationOpportunities($branchId);
            
            // 4. MONTHLY RECOMMENDATIONS
            $results['recommendations'] = $this->getMonthlyRecommendations($results);
            
            // 5. SUMMARY
            $results['summary'] = $this->generateMonthlySummary($results);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error('Monthly stock check failed: ' . $e->getMessage());
            return ['error' => $e->getMessage(), 'check_type' => 'monthly'];
        }
    }

    // DAILY CHECK METHODS
    private function getCriticalAlerts($branchId)
    {
        $query = InventoryItem::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $alerts = [];
        
        // Items with zero stock that are critical
        $criticalOutOfStock = $query->clone()
            ->where('current_stock', 0)
            ->where('status', 'active')
            ->get();

        foreach ($criticalOutOfStock as $item) {
            $alerts[] = [
                'type' => 'critical_out_of_stock',
                'item' => $item->name,
                'item_id' => $item->id,
                'severity' => 'high',
                'message' => "Critical item {$item->name} is out of stock!",
                'action_required' => 'immediate_order'
            ];
        }

        // Items with very low stock (below 10% of reorder point)
        $veryLowStock = $query->clone()
            ->where('current_stock', '>', 0)
            ->where('current_stock', '<', DB::raw('reorder_point * 0.1'))
            ->where('status', 'active')
            ->get();

        foreach ($veryLowStock as $item) {
            $alerts[] = [
                'type' => 'very_low_stock',
                'item' => $item->name,
                'item_id' => $item->id,
                'severity' => 'medium',
                'message' => "Item {$item->name} has very low stock ({$item->current_stock} units)",
                'action_required' => 'urgent_order'
            ];
        }

        return $alerts;
    }

    private function getOutOfStockItems($branchId)
    {
        $query = InventoryItem::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->where('current_stock', 0)
            ->where('status', 'active')
            ->with(['category', 'supplier'])
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'category' => $item->category->name ?? 'Uncategorized',
                    'supplier' => $item->supplier->name ?? 'No Supplier',
                    'reorder_point' => $item->reorder_point,
                    'unit_price' => $item->unit_price,
                    'last_restock_date' => $item->last_restock_date
                ];
            });
    }

    private function getLowStockItems($branchId)
    {
        $query = InventoryItem::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query->where('current_stock', '>', 0)
            ->where('current_stock', '<=', DB::raw('reorder_point'))
            ->where('status', 'active')
            ->with(['category', 'supplier'])
            ->get()
            ->map(function ($item) {
                $stockLevel = ($item->current_stock / $item->reorder_point) * 100;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'current_stock' => $item->current_stock,
                    'reorder_point' => $item->reorder_point,
                    'stock_level_percentage' => round($stockLevel, 1),
                    'category' => $item->category->name ?? 'Uncategorized',
                    'supplier' => $item->supplier->name ?? 'No Supplier',
                    'unit_price' => $item->unit_price,
                    'recommended_order' => max($item->reorder_point - $item->current_stock, $item->reorder_point * 0.5)
                ];
            });
    }

    private function getDailyRecommendations($results)
    {
        $recommendations = [];

        // Critical alerts recommendations
        if (!empty($results['critical_alerts'])) {
            $recommendations[] = [
                'type' => 'immediate_action',
                'priority' => 'high',
                'message' => 'Address critical stock alerts immediately',
                'actions' => [
                    'Place emergency orders for out-of-stock critical items',
                    'Contact suppliers for urgent deliveries',
                    'Consider alternative suppliers for critical items'
                ]
            ];
        }

        // Low stock recommendations
        if (!empty($results['low_stock_items'])) {
            $recommendations[] = [
                'type' => 'ordering',
                'priority' => 'medium',
                'message' => 'Place orders for low stock items',
                'actions' => [
                    'Review and place orders for items below reorder point',
                    'Consider bulk ordering for frequently used items',
                    'Check supplier lead times'
                ]
            ];
        }

        return $recommendations;
    }

    private function generateDailySummary($results)
    {
        return [
            'total_items_checked' => InventoryItem::count(),
            'critical_alerts_count' => count($results['critical_alerts']),
            'out_of_stock_count' => count($results['out_of_stock_items']),
            'low_stock_count' => count($results['low_stock_items']),
            'overall_status' => $this->getOverallStatus($results),
            'next_check_recommended' => now()->addDay()->format('Y-m-d H:i:s')
        ];
    }

    // WEEKLY CHECK METHODS
    private function getWeeklyTrends($branchId)
    {
        $lastWeek = now()->subWeek();
        
        $query = InventoryTransaction::where('created_at', '>=', $lastWeek);
        if ($branchId) {
            $query->whereHas('item', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $transactions = $query->with('item')
            ->get()
            ->groupBy('inventory_item_id');

        $trends = [];
        foreach ($transactions as $itemId => $itemTransactions) {
            $item = $itemTransactions->first()->item;
            $weeklyUsage = $itemTransactions->where('type', 'sale')->sum('quantity');
            $weeklyPurchases = $itemTransactions->where('type', 'purchase')->sum('quantity');
            
            $trends[] = [
                'item_id' => $itemId,
                'item_name' => $item->name,
                'weekly_usage' => $weeklyUsage,
                'weekly_purchases' => $weeklyPurchases,
                'net_change' => $weeklyPurchases - $weeklyUsage,
                'current_stock' => $item->current_stock,
                'reorder_point' => $item->reorder_point
            ];
        }

        return $trends;
    }

    private function getSupplierPerformance($branchId)
    {
        $recentOrders = InventoryOrder::where('created_at', '>=', now()->subWeek())
            ->with(['items.item', 'supplier'])
            ->get();

        $supplierPerformance = [];
        foreach ($recentOrders as $order) {
            $supplierId = $order->supplier_id;
            if (!isset($supplierPerformance[$supplierId])) {
                $supplierPerformance[$supplierId] = [
                    'supplier_name' => $order->supplier->name ?? 'Unknown',
                    'total_orders' => 0,
                    'total_items_ordered' => 0,
                    'total_items_received' => 0
                ];
            }

            $supplierPerformance[$supplierId]['total_orders']++;
            
            $totalOrdered = $order->items->sum('quantity');
            $totalReceived = $order->items->sum('received_quantity');
            $supplierPerformance[$supplierId]['total_items_ordered'] += $totalOrdered;
            $supplierPerformance[$supplierId]['total_items_received'] += $totalReceived;
        }

        // Calculate fulfillment rates
        foreach ($supplierPerformance as &$performance) {
            if ($performance['total_items_ordered'] > 0) {
                $performance['fulfillment_rate'] = 
                    ($performance['total_items_received'] / $performance['total_items_ordered']) * 100;
            } else {
                $performance['fulfillment_rate'] = 0;
            }
        }

        return array_values($supplierPerformance);
    }

    private function getWeeklyCostAnalysis($branchId)
    {
        $lastWeek = now()->subWeek();
        
        $query = InventoryTransaction::where('created_at', '>=', $lastWeek);
        if ($branchId) {
            $query->whereHas('item', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $transactions = $query->get();

        $totalPurchases = $transactions->where('type', 'purchase')->sum('total_amount');
        $totalSales = $transactions->where('type', 'sale')->sum('total_amount');
        $totalWaste = $transactions->where('type', 'waste')->sum('total_amount');

        return [
            'total_purchases' => $totalPurchases,
            'total_sales' => $totalSales,
            'total_waste' => $totalWaste,
            'net_cost' => $totalPurchases - $totalSales + $totalWaste,
            'waste_percentage' => $totalPurchases > 0 ? ($totalWaste / $totalPurchases) * 100 : 0
        ];
    }

    private function getWeeklyRecommendations($results)
    {
        $recommendations = [];

        // Supplier performance recommendations
        if (!empty($results['supplier_performance'])) {
            $poorPerformers = collect($results['supplier_performance'])
                ->where('fulfillment_rate', '<', 80)
                ->count();
            
            if ($poorPerformers > 0) {
                $recommendations[] = [
                    'type' => 'supplier_management',
                    'priority' => 'medium',
                    'message' => "{$poorPerformers} suppliers with poor performance",
                    'actions' => [
                        'Contact underperforming suppliers',
                        'Consider alternative suppliers',
                        'Review supplier contracts and terms'
                    ]
                ];
            }
        }

        // Cost optimization recommendations
        if (isset($results['cost_analysis']['waste_percentage']) && 
            $results['cost_analysis']['waste_percentage'] > 5) {
            $recommendations[] = [
                'type' => 'cost_optimization',
                'priority' => 'high',
                'message' => 'High waste percentage detected',
                'actions' => [
                    'Review ordering quantities to reduce waste',
                    'Implement better inventory rotation (FIFO)',
                    'Train staff on proper storage and handling'
                ]
            ];
        }

        return $recommendations;
    }

    private function generateWeeklySummary($results)
    {
        return [
            'trends_analyzed' => count($results['trend_analysis']),
            'suppliers_evaluated' => count($results['supplier_performance']),
            'total_weekly_cost' => $results['cost_analysis']['total_purchases'] ?? 0,
            'recommendations_count' => count($results['recommendations']),
            'overall_status' => $this->getOverallStatus($results),
            'next_check_recommended' => now()->addWeek()->format('Y-m-d H:i:s')
        ];
    }

    // MONTHLY CHECK METHODS
    private function getStrategicAnalysis($branchId)
    {
        $lastMonth = now()->subMonth();
        
        $query = InventoryTransaction::where('created_at', '>=', $lastMonth);
        if ($branchId) {
            $query->whereHas('item', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $transactions = $query->get();

        $totalSales = $transactions->where('type', 'sale')->sum('total_amount');
        $totalPurchases = $transactions->where('type', 'purchase')->sum('total_amount');
        $totalWaste = $transactions->where('type', 'waste')->sum('total_amount');

        return [
            'monthly_sales' => $totalSales,
            'monthly_purchases' => $totalPurchases,
            'monthly_waste' => $totalWaste,
            'gross_margin' => $totalSales - $totalPurchases,
            'waste_percentage' => $totalPurchases > 0 ? ($totalWaste / $totalPurchases) * 100 : 0
        ];
    }

    private function getProfitabilityAnalysis($branchId)
    {
        $lastMonth = now()->subMonth();
        
        $query = InventoryItem::query();
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $items = $query->get();
        $profitabilityData = [];

        foreach ($items as $item) {
            $monthlySales = InventoryTransaction::where('inventory_item_id', $item->id)
                ->where('type', 'sale')
                ->where('created_at', '>=', $lastMonth)
                ->sum('total_amount');

            $monthlyPurchases = InventoryTransaction::where('inventory_item_id', $item->id)
                ->where('type', 'purchase')
                ->where('created_at', '>=', $lastMonth)
                ->sum('total_amount');

            $profitability = $monthlySales - $monthlyPurchases;
            $profitMargin = $monthlySales > 0 ? ($profitability / $monthlySales) * 100 : 0;

            $profitabilityData[] = [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'monthly_sales' => $monthlySales,
                'monthly_purchases' => $monthlyPurchases,
                'profitability' => $profitability,
                'profit_margin' => $profitMargin
            ];
        }

        // Sort by profitability
        usort($profitabilityData, function ($a, $b) {
            return $b['profitability'] <=> $a['profitability'];
        });

        return $profitabilityData;
    }

    private function getOptimizationOpportunities($branchId)
    {
        $opportunities = [];

        // Overstocked items
        $overstockedItems = InventoryItem::where('current_stock', '>', DB::raw('reorder_point * 2'));
        if ($branchId) {
            $overstockedItems->where('branch_id', $branchId);
        }

        $overstockedItems = $overstockedItems->get();
        foreach ($overstockedItems as $item) {
            $opportunities[] = [
                'type' => 'overstocked',
                'item_id' => $item->id,
                'item_name' => $item->name,
                'current_stock' => $item->current_stock,
                'reorder_point' => $item->reorder_point,
                'excess_stock' => $item->current_stock - $item->reorder_point,
                'recommendation' => 'Reduce reorder point or consider promotions'
            ];
        }

        return $opportunities;
    }

    private function getMonthlyRecommendations($results)
    {
        $recommendations = [];

        // Strategic recommendations based on profitability
        if (!empty($results['profitability_analysis'])) {
            $topPerformers = collect($results['profitability_analysis'])
                ->take(5)
                ->where('profit_margin', '>', 20);
            
            if ($topPerformers->count() > 0) {
                $recommendations[] = [
                    'type' => 'profitability',
                    'priority' => 'high',
                    'message' => 'Focus on high-profit margin items',
                    'actions' => [
                        'Increase stock levels for high-profit items',
                        'Consider premium pricing for top performers',
                        'Develop marketing campaigns for profitable items'
                    ]
                ];
            }
        }

        // Optimization recommendations
        if (!empty($results['optimization_opportunities'])) {
            $overstockedCount = collect($results['optimization_opportunities'])
                ->where('type', 'overstocked')
                ->count();
            
            if ($overstockedCount > 0) {
                $recommendations[] = [
                    'type' => 'optimization',
                    'priority' => 'medium',
                    'message' => "{$overstockedCount} items are overstocked",
                    'actions' => [
                        'Review and adjust reorder points',
                        'Consider promotional pricing for overstocked items',
                        'Implement better demand forecasting'
                    ]
                ];
            }
        }

        return $recommendations;
    }

    private function generateMonthlySummary($results)
    {
        return [
            'strategic_insights' => count($results['strategic_analysis']),
            'profitability_items' => count($results['profitability_analysis']),
            'optimization_opportunities' => count($results['optimization_opportunities']),
            'recommendations_count' => count($results['recommendations']),
            'overall_status' => $this->getOverallStatus($results),
            'next_check_recommended' => now()->addMonth()->format('Y-m-d H:i:s')
        ];
    }

    private function getOverallStatus($results)
    {
        $criticalCount = count($results['critical_alerts'] ?? []);
        $outOfStockCount = count($results['out_of_stock_items'] ?? []);
        
        if ($criticalCount > 0) {
            return 'critical';
        } elseif ($outOfStockCount > 5) {
            return 'warning';
        } else {
            return 'good';
        }
    }
} 