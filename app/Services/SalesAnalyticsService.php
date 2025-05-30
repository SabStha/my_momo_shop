<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesAnalyticsService
{
    /**
     * Get total sales for a given period
     */
    public function getTotalSales($startDate = null, $endDate = null)
    {
        $query = Order::where('status', 'completed');
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->sum('total_amount');
    }

    /**
     * Get total number of orders for a given period
     */
    public function getTotalOrders($startDate = null, $endDate = null)
    {
        $query = Order::where('status', 'completed');
        
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query->count();
    }

    /**
     * Get average order value
     */
    public function getAverageOrderValue($startDate = null, $endDate = null)
    {
        $totalSales = $this->getTotalSales($startDate, $endDate);
        $totalOrders = $this->getTotalOrders($startDate, $endDate);
        
        return $totalOrders > 0 ? $totalSales / $totalOrders : 0;
    }

    /**
     * Get top selling products
     */
    public function getTopSellingProducts($limit = 5, $startDate = null, $endDate = null)
    {
        $query = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'completed')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit);

        if ($startDate) {
            $query->where('orders.created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('orders.created_at', '<=', $endDate);
        }

        return $query->get()->map(function ($item) {
            $product = Product::find($item->product_id);
            return [
                'product' => $product,
                'total_quantity' => $item->total_quantity
            ];
        });
    }

    /**
     * Get sales by day for the last 30 days
     */
    public function getDailySales($days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        return Order::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get all dashboard KPIs
     */
    public function getDashboardKPIs()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        
        return [
            'today' => [
                'sales' => $this->getTotalSales($today),
                'orders' => $this->getTotalOrders($today),
                'average_order_value' => $this->getAverageOrderValue($today),
            ],
            'this_month' => [
                'sales' => $this->getTotalSales($thisMonth),
                'orders' => $this->getTotalOrders($thisMonth),
                'average_order_value' => $this->getAverageOrderValue($thisMonth),
            ],
            'last_month' => [
                'sales' => $this->getTotalSales($lastMonth, $thisMonth),
                'orders' => $this->getTotalOrders($lastMonth, $thisMonth),
                'average_order_value' => $this->getAverageOrderValue($lastMonth, $thisMonth),
            ],
            'top_products' => $this->getTopSellingProducts(5),
            'daily_sales' => $this->getDailySales(),
        ];
    }
} 