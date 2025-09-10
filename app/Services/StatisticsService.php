<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductRating;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                'orders_delivered' => $this->getOrdersDeliveredCount(),
                'years_in_business' => $this->getYearsInBusiness(),
                'growth_percentage' => $this->getGrowthPercentage(),
                'satisfaction_rate' => $this->getSatisfactionRate(),
                'average_delivery_time' => $this->getAverageDeliveryTime(),
            ];
        });
    }

    /**
     * Get count of happy customers (unique customers who have completed orders)
     */
    public function getHappyCustomersCount()
    {
        try {
            return Order::where('status', 'completed')
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');
        } catch (\Exception $e) {
            Log::error('Error getting happy customers count: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get count of active momo varieties (products)
     */
    public function getMomoVarietiesCount()
    {
        try {
            return Product::where('is_active', true)
                ->where('category', 'Momo')
                ->count();
        } catch (\Exception $e) {
            Log::error('Error getting momo varieties count: ' . $e->getMessage());
            return 15;
        }
    }

    /**
     * Get average customer rating
     */
    public function getAverageCustomerRating()
    {
        try {
            $ratings = ProductRating::where('rating', '>', 0)->get();
            
            if ($ratings->isEmpty()) {
                return null; // No ratings available yet
            }
            
            $totalRating = $ratings->sum('rating');
            $averageRating = $totalRating / $ratings->count();
            
            // Round to 1 decimal place
            return round($averageRating, 1);
        } catch (\Exception $e) {
            Log::error('Error getting average customer rating: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get total orders count
     */
    public function getTotalOrdersCount()
    {
        try {
            return Order::where('status', 'completed')->count();
        } catch (\Exception $e) {
            Log::error('Error getting total orders count: ' . $e->getMessage());
            return 1000;
        }
    }

    /**
     * Get total revenue
     */
    public function getTotalRevenue()
    {
        try {
            return Order::where('status', 'completed')
                ->sum('total');
        } catch (\Exception $e) {
            Log::error('Error getting total revenue: ' . $e->getMessage());
            return 50000;
        }
    }

    /**
     * Get count of orders delivered (completed orders)
     */
    public function getOrdersDeliveredCount()
    {
        try {
            return Order::where('status', 'completed')->count();
        } catch (\Exception $e) {
            Log::error('Error getting orders delivered count: ' . $e->getMessage());
            return 1500;
        }
    }

    /**
     * Get years in business (based on first order or user registration)
     */
    public function getYearsInBusiness()
    {
        try {
            $firstOrder = Order::orderBy('created_at', 'asc')->first();
            $firstUser = User::orderBy('created_at', 'asc')->first();
            
            $startDate = null;
            if ($firstOrder && $firstUser) {
                $startDate = $firstOrder->created_at < $firstUser->created_at ? 
                    $firstOrder->created_at : $firstUser->created_at;
            } elseif ($firstOrder) {
                $startDate = $firstOrder->created_at;
            } elseif ($firstUser) {
                $startDate = $firstUser->created_at;
            } else {
                // Fallback to a reasonable default
                return 3;
            }
            
            $years = now()->diffInYears($startDate);
            return max(1, $years); // At least 1 year
        } catch (\Exception $e) {
            Log::error('Error getting years in business: ' . $e->getMessage());
            return 3;
        }
    }

    /**
     * Get growth percentage (comparing this month to last month)
     */
    public function getGrowthPercentage()
    {
        try {
            $thisMonth = Order::where('status', 'completed')
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();
            
            $lastMonth = Order::where('status', 'completed')
                ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
                ->count();
            
            if ($lastMonth == 0) {
                return 15; // Default growth if no previous data
            }
            
            $growth = (($thisMonth - $lastMonth) / $lastMonth) * 100;
            return round($growth, 0);
        } catch (\Exception $e) {
            Log::error('Error getting growth percentage: ' . $e->getMessage());
            return 15;
        }
    }

    /**
     * Get satisfaction rate (percentage of 4-5 star ratings)
     */
    public function getSatisfactionRate()
    {
        try {
            $totalRatings = ProductRating::count();
            if ($totalRatings == 0) {
                return 98; // Default satisfaction rate
            }
            
            $satisfiedRatings = ProductRating::whereIn('rating', [4, 5])->count();
            $satisfactionRate = ($satisfiedRatings / $totalRatings) * 100;
            return round($satisfactionRate, 0);
        } catch (\Exception $e) {
            Log::error('Error getting satisfaction rate: ' . $e->getMessage());
            return 98;
        }
    }

    /**
     * Get average delivery time in minutes
     */
    public function getAverageDeliveryTime()
    {
        try {
            // Get completed orders with delivery time data
            $orders = Order::where('status', 'completed')
                ->whereNotNull('delivered_at')
                ->whereNotNull('created_at')
                ->get();
            
            if ($orders->isEmpty()) {
                // If no delivery data, calculate from order processing time
                $recentOrders = Order::with('items') // Load the relationship
                    ->where('status', 'completed')
                    ->where('created_at', '>=', now()->subDays(30))
                    ->get();
                
                if ($recentOrders->isEmpty()) {
                    return 25; // Default fallback
                }
                
                // Estimate delivery time based on order processing patterns
                $totalMinutes = 0;
                $count = 0;
                
                foreach ($recentOrders as $order) {
                    // Estimate delivery time based on order size and time of day
                    $baseTime = 20; // Base delivery time
                    
                    // Safely get item count
                    $itemCount = $order->items ? $order->items->count() : 1;
                    
                    // Add time for larger orders
                    if ($itemCount > 5) {
                        $baseTime += 5;
                    }
                    
                    // Add time for peak hours (lunch/dinner)
                    $hour = $order->created_at->hour;
                    if (($hour >= 11 && $hour <= 14) || ($hour >= 17 && $hour <= 20)) {
                        $baseTime += 5;
                    }
                    
                    $totalMinutes += $baseTime;
                    $count++;
                }
                
                return $count > 0 ? round($totalMinutes / $count) : 25;
            }
            
            // Calculate actual delivery time from delivered_at - created_at
            $totalMinutes = 0;
            $count = 0;
            
            foreach ($orders as $order) {
                $deliveryTime = $order->delivered_at->diffInMinutes($order->created_at);
                if ($deliveryTime > 0 && $deliveryTime < 120) { // Reasonable delivery time range
                    $totalMinutes += $deliveryTime;
                    $count++;
                }
            }
            
            return $count > 0 ? round($totalMinutes / $count) : 25;
        } catch (\Exception $e) {
            Log::error('Error getting average delivery time: ' . $e->getMessage());
            return 25;
        }
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
            'orders_delivered' => $this->formatNumber($stats['orders_delivered']),
            'years_in_business' => $stats['years_in_business'] . '+',
            'growth_percentage' => $stats['growth_percentage'],
            'satisfaction_rate' => $stats['satisfaction_rate'],
            'average_delivery_time' => $stats['average_delivery_time'],
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
        return 'Rs.' . number_format($amount, 0);
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

    /**
     * Get real customer testimonials from product ratings
     */
    public function getCustomerTestimonials($limit = 6)
    {
        try {
            return ProductRating::with(['user', 'product'])
                ->whereNotNull('review')
                ->where('rating', '>=', 4)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($rating) {
                    $userName = $rating->user ? $rating->user->name : 'Anonymous';
                    $initials = $this->getInitials($userName);
                    $color = $this->getRandomColor();
                    
                    return [
                        'avatar' => $initials,
                        'color' => $color,
                        'name' => $userName,
                        'stars' => $rating->rating,
                        'comment' => $rating->review,
                        'order' => $rating->product ? $rating->product->name : 'Momo Order',
                        'date' => $rating->created_at->diffForHumans()
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting customer testimonials: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get initials from name
     */
    private function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';
        
        foreach ($words as $word) {
            if (strlen($word) > 0) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return substr($initials, 0, 2);
    }

    /**
     * Get random gradient color for avatar
     */
    private function getRandomColor()
    {
        $colors = [
            'from-red-400 to-red-600',
            'from-blue-400 to-blue-600',
            'from-green-400 to-green-600',
            'from-purple-400 to-purple-600',
            'from-orange-400 to-orange-600',
            'from-pink-400 to-pink-600',
            'from-indigo-400 to-indigo-600',
            'from-teal-400 to-teal-600'
        ];
        
        return $colors[array_rand($colors)];
    }
} 