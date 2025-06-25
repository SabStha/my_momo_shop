<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * Get all homepage statistics
     */
    public function getHomepageStatistics()
    {
        return Cache::remember('homepage_statistics', 3600, function () {
            return [
                'happy_customers' => $this->getHappyCustomersCount(),
                'momo_varieties' => $this->getMomoVarietiesCount(),
                'customer_rating' => $this->getAverageCustomerRating(),
                'total_orders' => $this->getTotalOrdersCount(),
                'total_revenue' => $this->getTotalRevenue(),
            ];
        });
    }

    /**
     * Get count of happy customers (unique customers who have completed orders)
     */
    public function getHappyCustomersCount()
    {
        return Order::where('status', 'completed')
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
    }

    /**
     * Get count of active momo varieties (products)
     */
    public function getMomoVarietiesCount()
    {
        return Product::where('is_active', true)
            ->where('category', 'Momo')
            ->count();
    }

    /**
     * Get average customer rating
     */
    public function getAverageCustomerRating()
    {
        $averageRating = ProductRating::avg('rating');
        
        // Return 5.0 if no ratings exist (fallback)
        return $averageRating ? round($averageRating, 1) : 5.0;
    }

    /**
     * Get total orders count
     */
    public function getTotalOrdersCount()
    {
        return Order::where('status', 'completed')->count();
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue()
    {
        return Order::where('status', 'completed')
            ->sum('total');
    }

    /**
     * Clear statistics cache
     */
    public function clearCache()
    {
        Cache::forget('homepage_statistics');
    }

    /**
     * Get statistics with formatted numbers
     */
    public function getFormattedStatistics()
    {
        $stats = $this->getHomepageStatistics();
        
        return [
            'happy_customers' => $this->formatNumber($stats['happy_customers']),
            'momo_varieties' => $this->formatNumber($stats['momo_varieties']),
            'customer_rating' => $stats['customer_rating'],
            'total_orders' => $this->formatNumber($stats['total_orders']),
            'total_revenue' => $this->formatCurrency($stats['total_revenue']),
        ];
    }

    /**
     * Format number with K, M suffixes
     */
    private function formatNumber($number)
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M+';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'K+';
        }
        return $number . '+';
    }

    /**
     * Format currency
     */
    private function formatCurrency($amount)
    {
        return '$' . number_format($amount, 0);
    }

    /**
     * Get detailed statistics for admin dashboard
     */
    public function getDetailedStatistics()
    {
        return Cache::remember('detailed_statistics', 1800, function () {
            return [
                'orders' => [
                    'total' => Order::count(),
                    'completed' => Order::where('status', 'completed')->count(),
                    'pending' => Order::where('status', 'pending')->count(),
                    'processing' => Order::where('status', 'processing')->count(),
                    'cancelled' => Order::where('status', 'cancelled')->count(),
                ],
                'customers' => [
                    'total' => User::where('role', 'customer')->count(),
                    'active' => User::where('role', 'customer')
                        ->whereHas('orders', function($q) {
                            $q->where('created_at', '>=', now()->subDays(30));
                        })->count(),
                ],
                'products' => [
                    'total' => Product::count(),
                    'active' => Product::where('is_active', true)->count(),
                    'featured' => Product::where('is_featured', true)->count(),
                ],
                'ratings' => [
                    'total' => ProductRating::count(),
                    'average' => ProductRating::avg('rating'),
                    'distribution' => $this->getRatingDistribution(),
                ],
                'revenue' => [
                    'total' => Order::where('status', 'completed')->sum('total'),
                    'monthly' => Order::where('status', 'completed')
                        ->where('created_at', '>=', now()->startOfMonth())
                        ->sum('total'),
                    'weekly' => Order::where('status', 'completed')
                        ->where('created_at', '>=', now()->startOfWeek())
                        ->sum('total'),
                ],
            ];
        });
    }

    /**
     * Get rating distribution (1-5 stars)
     */
    private function getRatingDistribution()
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = ProductRating::where('rating', $i)->count();
        }
        return $distribution;
    }

    /**
     * Get statistics for a specific branch
     */
    public function getBranchStatistics($branchId)
    {
        return Cache::remember("branch_statistics_{$branchId}", 1800, function () use ($branchId) {
            return [
                'orders' => Order::where('branch_id', $branchId)->count(),
                'completed_orders' => Order::where('branch_id', $branchId)
                    ->where('status', 'completed')->count(),
                'revenue' => Order::where('branch_id', $branchId)
                    ->where('status', 'completed')->sum('total'),
                'products' => Product::where('branch_id', $branchId)
                    ->where('is_active', true)->count(),
            ];
        });
    }

    /**
     * Get trending statistics (last 7 days)
     */
    public function getTrendingStatistics()
    {
        $sevenDaysAgo = now()->subDays(7);
        
        return [
            'new_orders' => Order::where('created_at', '>=', $sevenDaysAgo)->count(),
            'new_customers' => User::where('created_at', '>=', $sevenDaysAgo)
                ->where('role', 'customer')->count(),
            'new_ratings' => ProductRating::where('created_at', '>=', $sevenDaysAgo)->count(),
            'revenue' => Order::where('status', 'completed')
                ->where('created_at', '>=', $sevenDaysAgo)->sum('total'),
        ];
    }
} 