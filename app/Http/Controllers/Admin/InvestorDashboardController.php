<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Campaign;
use App\Models\Employee;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Payment;
use App\Models\ChurnPrediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvestorDashboardController extends Controller
{
    public function index()
    {
        // Get date range (default to last 12 months)
        $endDate = now();
        $startDate = now()->subMonths(12);
        
        // Financial Performance
        $financialMetrics = $this->getFinancialMetrics($startDate, $endDate);
        
        // Business Growth
        $growthMetrics = $this->getGrowthMetrics($startDate, $endDate);
        
        // Operational Efficiency
        $operationalMetrics = $this->getOperationalMetrics($startDate, $endDate);
        
        // Risk Assessment
        $riskMetrics = $this->getRiskMetrics($startDate, $endDate);
        
        // Investment Highlights
        $investmentMetrics = $this->getInvestmentMetrics($startDate, $endDate);
        
        // Future Projections
        $projectionMetrics = $this->getProjectionMetrics($startDate, $endDate);
        
        // Branch Performance
        $branchPerformance = $this->getBranchPerformance($startDate, $endDate);
        
        // Monthly Trends
        $monthlyTrends = $this->getMonthlyTrends($startDate, $endDate);
        
        return view('admin.investor.dashboard', compact(
            'financialMetrics',
            'growthMetrics', 
            'operationalMetrics',
            'riskMetrics',
            'investmentMetrics',
            'projectionMetrics',
            'branchPerformance',
            'monthlyTrends'
        ));
    }

    private function getFinancialMetrics($startDate, $endDate)
    {
        // Total Revenue
        $totalRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // Cost of Goods Sold
        $costOfGoods = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum(DB::raw('order_items.quantity * products.cost_price'));

        // Gross Profit
        $grossProfit = $totalRevenue - $costOfGoods;
        $grossMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        // Operating Expenses (estimated as 30% of revenue)
        $operatingExpenses = $totalRevenue * 0.3;
        $netProfit = $grossProfit - $operatingExpenses;
        $netMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Previous period for comparison
        $prevStartDate = $startDate->copy()->subMonths(12);
        $prevEndDate = $endDate->copy()->subMonths(12);
        
        $prevRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('total_amount');

        $revenueGrowth = $prevRevenue > 0 ? (($totalRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;

        // Average Order Value
        $totalOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return [
            'total_revenue' => $totalRevenue,
            'gross_profit' => $grossProfit,
            'gross_margin' => $grossMargin,
            'net_profit' => $netProfit,
            'net_margin' => $netMargin,
            'revenue_growth' => $revenueGrowth,
            'average_order_value' => $averageOrderValue,
            'total_orders' => $totalOrders,
            'cost_of_goods' => $costOfGoods,
            'operating_expenses' => $operatingExpenses
        ];
    }

    private function getGrowthMetrics($startDate, $endDate)
    {
        // Customer Growth
        $totalCustomers = Customer::whereBetween('created_at', [$startDate, $endDate])->count();
        $activeCustomers = Customer::where('is_active', true)->count();
        
        // Previous period
        $prevStartDate = $startDate->copy()->subMonths(12);
        $prevEndDate = $endDate->copy()->subMonths(12);
        $prevCustomers = Customer::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
        
        $customerGrowth = $prevCustomers > 0 ? (($totalCustomers - $prevCustomers) / $prevCustomers) * 100 : 0;

        // Customer Lifetime Value
        $customerLifetimeValue = Customer::where('is_active', true)
            ->avg('total_spent') ?? 0;

        // Repeat Purchase Rate
        $repeatCustomers = Customer::where('total_orders', '>', 1)->count();
        $repeatPurchaseRate = $activeCustomers > 0 ? ($repeatCustomers / $activeCustomers) * 100 : 0;

        // Branch Growth
        $totalBranches = Branch::where('is_active', true)->count();
        $newBranches = Branch::whereBetween('created_at', [$startDate, $endDate])->count();

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'customer_growth' => $customerGrowth,
            'customer_lifetime_value' => $customerLifetimeValue,
            'repeat_purchase_rate' => $repeatPurchaseRate,
            'total_branches' => $totalBranches,
            'new_branches' => $newBranches
        ];
    }

    private function getOperationalMetrics($startDate, $endDate)
    {
        // Inventory Turnover
        $inventoryValue = InventoryItem::sum(DB::raw('current_stock * unit_price'));
        $costOfGoodsSold = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum(DB::raw('order_items.quantity * products.cost_price'));
        
        $inventoryTurnover = $inventoryValue > 0 ? $costOfGoodsSold / $inventoryValue : 0;

        // Employee Productivity
        $totalEmployees = Employee::count();
        $revenuePerEmployee = $totalEmployees > 0 ? 
            Order::where('status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total_amount') / $totalEmployees : 0;

        // Campaign ROI
        $campaigns = Campaign::whereBetween('created_at', [$startDate, $endDate])->get();
        $totalCampaignCost = $campaigns->sum('cost');
        $totalCampaignRevenue = $campaigns->sum('total_revenue');
        $campaignROI = $totalCampaignCost > 0 ? (($totalCampaignRevenue - $totalCampaignCost) / $totalCampaignCost) * 100 : 0;

        // Digital Payment Adoption
        $totalPayments = Payment::whereBetween('created_at', [$startDate, $endDate])->count();
        $digitalPayments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('payment_method', ['card', 'khalti', 'wallet'])
            ->count();
        
        $digitalAdoption = $totalPayments > 0 ? ($digitalPayments / $totalPayments) * 100 : 0;

        return [
            'inventory_turnover' => $inventoryTurnover,
            'revenue_per_employee' => $revenuePerEmployee,
            'campaign_roi' => $campaignROI,
            'digital_adoption' => $digitalAdoption,
            'total_employees' => $totalEmployees,
            'total_campaigns' => $campaigns->count()
        ];
    }

    private function getRiskMetrics($startDate, $endDate)
    {
        // Customer Churn Risk
        $highRiskCustomers = ChurnPrediction::where('churn_probability', '>', 0.7)->count();
        $totalCustomers = Customer::count();
        $churnRisk = $totalCustomers > 0 ? ($highRiskCustomers / $totalCustomers) * 100 : 0;

        // Inventory Risk (low stock items)
        $lowStockItems = InventoryItem::where('current_stock', '<=', 'reorder_point')->count();
        $totalItems = InventoryItem::count();
        $inventoryRisk = $totalItems > 0 ? ($lowStockItems / $totalItems) * 100 : 0;

        // Revenue Concentration Risk
        $branchRevenues = Branch::with(['orders' => function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->whereBetween('created_at', [$startDate, $endDate]);
        }])->get()->map(function($branch) {
            return $branch->orders->sum('total_amount');
        });

        $totalRevenue = $branchRevenues->sum();
        $maxBranchRevenue = $branchRevenues->max();
        $revenueConcentration = $totalRevenue > 0 ? ($maxBranchRevenue / $totalRevenue) * 100 : 0;

        return [
            'churn_risk' => $churnRisk,
            'inventory_risk' => $inventoryRisk,
            'revenue_concentration' => $revenueConcentration,
            'high_risk_customers' => $highRiskCustomers,
            'low_stock_items' => $lowStockItems
        ];
    }

    private function getInvestmentMetrics($startDate, $endDate)
    {
        // Revenue Multiple (assuming 3x for restaurant industry)
        $annualRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount') * 12; // Annualize the revenue

        $estimatedValuation = $annualRevenue * 3;

        // Growth Rate
        $currentRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $prevStartDate = $startDate->copy()->subMonths(12);
        $prevEndDate = $endDate->copy()->subMonths(12);
        
        $prevRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->sum('total_amount');

        $growthRate = $prevRevenue > 0 ? (($currentRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;

        // Market Penetration (estimated)
        $totalPopulation = 1000000; // Example: 1M population in target market
        $activeCustomers = Customer::where('is_active', true)->count();
        $marketPenetration = ($activeCustomers / $totalPopulation) * 100;

        return [
            'estimated_valuation' => $estimatedValuation,
            'annual_revenue' => $annualRevenue,
            'growth_rate' => $growthRate,
            'market_penetration' => $marketPenetration,
            'revenue_multiple' => 3
        ];
    }

    private function getProjectionMetrics($startDate, $endDate)
    {
        // Projected Revenue (assuming 15% growth)
        $currentRevenue = Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        $projectedRevenue = $currentRevenue * 1.15;

        // Expansion Opportunities
        $potentialMarkets = 5; // Number of potential new markets
        $estimatedMarketSize = 500000; // Estimated market size per location

        // Technology Investment Opportunities
        $totalPayments = Payment::whereBetween('created_at', [$startDate, $endDate])->count();
        $digitalPayments = Payment::whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('payment_method', ['card', 'khalti', 'wallet'])
            ->count();
        $digitalOrderPercentage = $totalPayments > 0 ? ($digitalPayments / $totalPayments) * 100 : 0;

        return [
            'projected_revenue' => $projectedRevenue,
            'potential_markets' => $potentialMarkets,
            'estimated_market_size' => $estimatedMarketSize,
            'digital_order_percentage' => $digitalOrderPercentage
        ];
    }

    private function getBranchPerformance($startDate, $endDate)
    {
        return Branch::with(['orders' => function($query) use ($startDate, $endDate) {
            $query->where('status', 'completed')
                  ->whereBetween('created_at', [$startDate, $endDate]);
        }])->get()->map(function($branch) {
            $revenue = $branch->orders->sum('total_amount');
            $orders = $branch->orders->count();
            $averageOrderValue = $orders > 0 ? $revenue / $orders : 0;
            
            return [
                'name' => $branch->name,
                'revenue' => $revenue,
                'orders' => $orders,
                'average_order_value' => $averageOrderValue,
                'is_active' => $branch->is_active
            ];
        })->sortByDesc('revenue')->values();
    }

    private function getMonthlyTrends($startDate, $endDate)
    {
        return Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'period' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'revenue' => $item->revenue,
                    'orders' => $item->orders
                ];
            });
    }
} 