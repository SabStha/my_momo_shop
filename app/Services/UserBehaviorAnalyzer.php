<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Analyzes user behavior to power personalized offers
 */
class UserBehaviorAnalyzer
{
    /**
     * Get comprehensive user behavior profile
     */
    public function getUserBehaviorProfile(User $user): array
    {
        $cacheKey = "user_behavior_{$user->id}";
        
        return Cache::remember($cacheKey, 300, function() use ($user) {
            return [
                'purchase_patterns' => $this->analyzePurchasePatterns($user),
                'product_preferences' => $this->analyzeProductPreferences($user),
                'timing_patterns' => $this->analyzeTimingPatterns($user),
                'value_metrics' => $this->calculateValueMetrics($user),
                'engagement_score' => $this->calculateEngagementScore($user),
                'churn_risk' => $this->assessChurnRisk($user),
                'recommendations' => $this->generateRecommendations($user),
            ];
        });
    }

    /**
     * Analyze purchase patterns
     */
    protected function analyzePurchasePatterns(User $user): array
    {
        $orders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        if ($orders->isEmpty()) {
            return [
                'total_orders' => 0,
                'frequency' => null,
                'average_gap_days' => null,
                'last_order_date' => null,
                'days_since_last_order' => null,
                'is_new_customer' => true,
            ];
        }
        
        $totalOrders = $orders->count();
        $firstOrderDate = $orders->last()->created_at;
        $lastOrderDate = $orders->first()->created_at;
        $daysSinceFirst = Carbon::now()->diffInDays($firstOrderDate);
        $daysSinceLast = Carbon::now()->diffInDays($lastOrderDate);
        
        // Calculate average days between orders
        $orderDates = $orders->pluck('created_at')->map(fn($date) => $date->timestamp)->toArray();
        $gaps = [];
        for ($i = 0; $i < count($orderDates) - 1; $i++) {
            $gaps[] = ($orderDates[$i] - $orderDates[$i + 1]) / 86400; // Convert to days
        }
        $averageGapDays = !empty($gaps) ? array_sum($gaps) / count($gaps) : null;
        
        return [
            'total_orders' => $totalOrders,
            'frequency' => $this->determineFrequency($averageGapDays),
            'average_gap_days' => $averageGapDays,
            'first_order_date' => $firstOrderDate,
            'last_order_date' => $lastOrderDate,
            'days_since_last_order' => $daysSinceLast,
            'is_new_customer' => $totalOrders <= 2,
            'is_regular' => $totalOrders >= 5 && $averageGapDays <= 14,
            'order_momentum' => $this->calculateMomentum($orders),
        ];
    }

    /**
     * Analyze product preferences
     */
    protected function analyzeProductPreferences(User $user): array
    {
        $orderItems = OrderItem::whereHas('order', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->with('product')
        ->get();
        
        if ($orderItems->isEmpty()) {
            return [
                'favorite_products' => [],
                'favorite_categories' => [],
                'preferred_price_range' => null,
                'typical_order_size' => null,
            ];
        }
        
        // Group by product and count
        $productCounts = $orderItems->groupBy('product_id')
            ->map(function($items) {
                return [
                    'product' => $items->first()->product,
                    'count' => $items->sum('quantity'),
                    'total_spent' => $items->sum(function($item) {
                        return $item->price * $item->quantity;
                    }),
                ];
            })
            ->sortByDesc('count')
            ->take(5);
        
        // Analyze categories
        $categoryCounts = $orderItems->groupBy(function($item) {
            return $item->product->category ?? 'other';
        })->map->count()->sortDesc();
        
        // Price range analysis
        $prices = $orderItems->pluck('price')->filter();
        $avgPrice = $prices->avg();
        
        return [
            'favorite_products' => $productCounts->map(function($item) {
                return [
                    'id' => $item['product']->id,
                    'name' => $item['product']->name,
                    'times_ordered' => $item['count'],
                    'total_spent' => $item['total_spent'],
                ];
            })->values()->toArray(),
            'favorite_categories' => $categoryCounts->keys()->take(3)->toArray(),
            'preferred_price_range' => [
                'min' => $prices->min(),
                'max' => $prices->max(),
                'average' => $avgPrice,
            ],
            'typical_order_size' => $orderItems->count() / max(Order::where('user_id', $user->id)->count(), 1),
        ];
    }

    /**
     * Analyze timing patterns (when user typically orders)
     */
    protected function analyzeTimingPatterns(User $user): array
    {
        $orders = Order::where('user_id', $user->id)->get();
        
        if ($orders->isEmpty()) {
            return [
                'preferred_hours' => [],
                'preferred_days' => [],
                'optimal_notification_time' => null,
            ];
        }
        
        // Analyze hours
        $hourCounts = $orders->groupBy(function($order) {
            return $order->created_at->hour;
        })->map->count()->sortDesc();
        
        // Analyze days of week
        $dayCounts = $orders->groupBy(function($order) {
            return $order->created_at->dayOfWeek;
        })->map->count()->sortDesc();
        
        // Determine optimal notification time (1-2 hours before typical order time)
        $mostCommonHour = $hourCounts->keys()->first();
        $optimalNotificationHour = $mostCommonHour ? max(9, $mostCommonHour - 2) : 11;
        
        return [
            'preferred_hours' => $hourCounts->keys()->take(3)->toArray(),
            'preferred_days' => $dayCounts->keys()->take(3)->toArray(),
            'optimal_notification_time' => sprintf('%02d:00:00', $optimalNotificationHour),
            'weekend_shopper' => $dayCounts->keys()->take(2)->contains(function($day) {
                return in_array($day, [0, 6]); // Sunday = 0, Saturday = 6
            }),
        ];
    }

    /**
     * Calculate value metrics
     */
    protected function calculateValueMetrics(User $user): array
    {
        $orders = Order::where('user_id', $user->id)->get();
        
        if ($orders->isEmpty()) {
            return [
                'lifetime_value' => 0,
                'average_order_value' => 0,
                'total_spent' => 0,
                'value_tier' => 'new',
            ];
        }
        
        $totalSpent = $orders->sum('total_amount');
        $averageOrderValue = $totalSpent / $orders->count();
        
        // Determine value tier
        $valueTier = 'bronze';
        if ($totalSpent >= 10000) {
            $valueTier = 'platinum';
        } elseif ($totalSpent >= 5000) {
            $valueTier = 'gold';
        } elseif ($totalSpent >= 2000) {
            $valueTier = 'silver';
        }
        
        return [
            'lifetime_value' => $totalSpent,
            'average_order_value' => $averageOrderValue,
            'total_spent' => $totalSpent,
            'value_tier' => $valueTier,
            'high_value_customer' => $totalSpent >= 5000,
        ];
    }

    /**
     * Calculate engagement score (0-100)
     */
    protected function calculateEngagementScore(User $user): int
    {
        $score = 0;
        
        // Order recency (30 points max)
        $daysSinceLast = $this->getDaysSinceLastOrder($user);
        if ($daysSinceLast !== null) {
            if ($daysSinceLast <= 7) {
                $score += 30;
            } elseif ($daysSinceLast <= 14) {
                $score += 20;
            } elseif ($daysSinceLast <= 30) {
                $score += 10;
            }
        }
        
        // Order frequency (30 points max)
        $totalOrders = Order::where('user_id', $user->id)->count();
        if ($totalOrders >= 10) {
            $score += 30;
        } elseif ($totalOrders >= 5) {
            $score += 20;
        } elseif ($totalOrders >= 2) {
            $score += 10;
        }
        
        // Total spend (20 points max)
        $totalSpent = Order::where('user_id', $user->id)->sum('total_amount');
        if ($totalSpent >= 5000) {
            $score += 20;
        } elseif ($totalSpent >= 2000) {
            $score += 15;
        } elseif ($totalSpent >= 500) {
            $score += 10;
        }
        
        // Account age (10 points max)
        $accountAgeDays = Carbon::now()->diffInDays($user->created_at);
        if ($accountAgeDays >= 180) {
            $score += 10;
        } elseif ($accountAgeDays >= 90) {
            $score += 7;
        } elseif ($accountAgeDays >= 30) {
            $score += 5;
        }
        
        // Reviews/engagement (10 points max)
        $reviewCount = $user->reviews()->count();
        if ($reviewCount >= 5) {
            $score += 10;
        } elseif ($reviewCount >= 2) {
            $score += 5;
        }
        
        return min(100, $score);
    }

    /**
     * Assess churn risk (low, medium, high)
     */
    protected function assessChurnRisk(User $user): string
    {
        $daysSinceLast = $this->getDaysSinceLastOrder($user);
        $totalOrders = Order::where('user_id', $user->id)->count();
        
        if ($daysSinceLast === null || $totalOrders === 0) {
            return 'new';
        }
        
        // High churn risk
        if ($daysSinceLast > 60 && $totalOrders >= 3) {
            return 'high';
        }
        
        // Medium churn risk
        if ($daysSinceLast > 30 && $totalOrders >= 2) {
            return 'medium';
        }
        
        // Low churn risk
        if ($daysSinceLast <= 14) {
            return 'low';
        }
        
        return 'medium';
    }

    /**
     * Generate personalized recommendations
     */
    protected function generateRecommendations(User $user): array
    {
        $recommendations = [];
        $patterns = $this->analyzePurchasePatterns($user);
        $churnRisk = $this->assessChurnRisk($user);
        $engagementScore = $this->calculateEngagementScore($user);
        
        // Recommend based on churn risk
        if ($churnRisk === 'high') {
            $recommendations[] = [
                'type' => 'win_back',
                'priority' => 'high',
                'suggested_discount' => 20,
                'message' => 'User at high churn risk - send aggressive win-back offer',
            ];
        } elseif ($churnRisk === 'medium') {
            $recommendations[] = [
                'type' => 'engagement',
                'priority' => 'medium',
                'suggested_discount' => 15,
                'message' => 'User engagement declining - send re-engagement offer',
            ];
        }
        
        // Recommend for new customers
        if ($patterns['is_new_customer']) {
            $recommendations[] = [
                'type' => 'welcome_bonus',
                'priority' => 'high',
                'suggested_discount' => 10,
                'message' => 'New customer - send welcome offer to encourage repeat purchase',
            ];
        }
        
        // Recommend for high-value customers
        if ($engagementScore >= 80) {
            $recommendations[] = [
                'type' => 'vip_exclusive',
                'priority' => 'medium',
                'suggested_discount' => 15,
                'message' => 'High-value customer - send VIP exclusive offer',
            ];
        }
        
        return $recommendations;
    }

    /**
     * Predict optimal offer for user
     */
    public function predictOptimalOffer(User $user): array
    {
        $profile = $this->getUserBehaviorProfile($user);
        $preferences = $profile['product_preferences'];
        $valueMetrics = $profile['value_metrics'];
        $churnRisk = $profile['churn_risk'];
        
        // Determine discount based on churn risk and value
        $discount = 10; // Base discount
        
        if ($churnRisk === 'high') {
            $discount = 25; // Aggressive win-back
        } elseif ($churnRisk === 'medium') {
            $discount = 15; // Moderate engagement
        } elseif ($valueMetrics['high_value_customer']) {
            $discount = 20; // VIP treatment
        }
        
        // Determine offer type
        $offerType = 'discount';
        if (!empty($preferences['favorite_products'])) {
            $offerType = 'targeted_product';
        }
        
        // Determine minimum purchase
        $minPurchase = max(20, $valueMetrics['average_order_value'] * 0.8);
        
        return [
            'discount' => $discount,
            'type' => $offerType,
            'min_purchase' => $minPurchase,
            'max_discount' => $discount * 3,
            'valid_days' => $churnRisk === 'high' ? 2 : 7,
            'target_products' => array_slice($preferences['favorite_products'], 0, 3),
            'reasoning' => $this->buildOfferReasoning($profile),
        ];
    }

    /**
     * Check if user should receive an offer now
     */
    public function shouldReceiveOffer(User $user, string $triggerType): bool
    {
        $profile = $this->getUserBehaviorProfile($user);
        
        switch ($triggerType) {
            case 'new_user_welcome':
                return $profile['purchase_patterns']['is_new_customer'] 
                    && $profile['purchase_patterns']['total_orders'] === 1;
                
            case 'abandoned_cart':
                return $this->hasAbandonedCart($user);
                
            case 'inactive_user':
                return $profile['purchase_patterns']['days_since_last_order'] >= 14;
                
            case 'high_value_vip':
                return $profile['value_metrics']['high_value_customer'];
                
            case 'churn_prevention':
                return in_array($profile['churn_risk'], ['medium', 'high']);
                
            default:
                return false;
        }
    }

    /**
     * Get optimal time to send offer to user
     */
    public function getOptimalSendTime(User $user): Carbon
    {
        $timingPatterns = $this->analyzeTimingPatterns($user);
        $optimalTime = $timingPatterns['optimal_notification_time'] ?? '11:00:00';
        
        // Parse time
        list($hour, $minute, $second) = explode(':', $optimalTime);
        
        // If user typically orders on weekends, schedule for Friday/Saturday
        $sendDate = Carbon::now();
        if ($timingPatterns['weekend_shopper'] ?? false) {
            $sendDate = $sendDate->next(Carbon::FRIDAY);
        }
        
        return $sendDate->setTime((int)$hour, (int)$minute, (int)$second);
    }

    // ===== HELPER METHODS =====

    protected function determineFrequency(?float $averageGapDays): ?string
    {
        if ($averageGapDays === null) return null;
        
        if ($averageGapDays <= 7) return 'weekly';
        if ($averageGapDays <= 14) return 'bi-weekly';
        if ($averageGapDays <= 30) return 'monthly';
        
        return 'occasional';
    }

    protected function calculateMomentum($orders): string
    {
        if ($orders->count() < 3) return 'insufficient_data';
        
        $recent = $orders->take(3);
        $older = $orders->slice(3, 3);
        
        $recentAvg = $recent->avg('total_amount');
        $olderAvg = $older->avg('total_amount');
        
        if ($olderAvg == 0) return 'growing';
        
        $change = (($recentAvg - $olderAvg) / $olderAvg) * 100;
        
        if ($change > 20) return 'accelerating';
        if ($change > 0) return 'growing';
        if ($change > -20) return 'stable';
        
        return 'declining';
    }

    protected function getDaysSinceLastOrder(User $user): ?int
    {
        $lastOrder = Order::where('user_id', $user->id)
            ->latest()
            ->first();
        
        return $lastOrder ? Carbon::now()->diffInDays($lastOrder->created_at) : null;
    }

    protected function hasAbandonedCart(User $user): bool
    {
        // Check if user has items in cart but hasn't ordered in 2+ hours
        // This would require a cart table - for now return false
        // TODO: Implement when cart persistence is added
        return false;
    }

    protected function buildOfferReasoning(array $profile): string
    {
        $reasons = [];
        
        if ($profile['churn_risk'] === 'high') {
            $reasons[] = 'User at risk of churning - aggressive win-back needed';
        }
        
        if ($profile['purchase_patterns']['is_new_customer']) {
            $reasons[] = 'New customer - encourage repeat purchase';
        }
        
        if ($profile['value_metrics']['high_value_customer']) {
            $reasons[] = 'High-value customer - maintain loyalty';
        }
        
        if ($profile['engagement_score'] >= 80) {
            $reasons[] = 'Highly engaged - reward loyalty';
        }
        
        return implode('. ', $reasons) ?: 'Standard personalized offer';
    }
}

