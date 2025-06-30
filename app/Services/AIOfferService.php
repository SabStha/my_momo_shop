<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\CustomerSegment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AIOfferService
{
    protected $openAIService;
    protected $customerAnalyticsService;
    protected $salesAnalyticsService;

    public function __construct(
        OpenAIService $openAIService,
        CustomerAnalyticsService $customerAnalyticsService,
        SalesAnalyticsService $salesAnalyticsService
    ) {
        $this->openAIService = $openAIService;
        $this->customerAnalyticsService = $customerAnalyticsService;
        $this->salesAnalyticsService = $salesAnalyticsService;
    }

    /**
     * Generate AI-powered offers automatically
     */
    public function generateAIOffers($branchId = 1)
    {
        try {
            // Collect business data for AI analysis
            $businessData = $this->collectBusinessData($branchId);
            
            // Generate offers using AI
            $aiOffers = $this->generateOffersWithAI($businessData);
            
            // Create and save offers
            $createdOffers = [];
            foreach ($aiOffers as $offerData) {
                $offer = $this->createOfferFromAI($offerData, $branchId);
                if ($offer) {
                    $createdOffers[] = $offer;
                }
            }
            
            return [
                'success' => true,
                'offers_created' => count($createdOffers),
                'offers' => $createdOffers
            ];
            
        } catch (\Exception $e) {
            \Log::error('AI Offer Generation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Generate personalized offers for specific user
     */
    public function generatePersonalizedOffers(User $user, $branchId = 1)
    {
        try {
            // Get user behavior data
            $userData = $this->getUserBehaviorData($user, $branchId);
            
            // Generate personalized offers
            $personalizedOffers = $this->generatePersonalizedOffersWithAI($userData);
            
            // Create temporary offers for the user
            $offers = [];
            foreach ($personalizedOffers as $offerData) {
                $offer = $this->createPersonalizedOffer($offerData, $user, $branchId);
                if ($offer) {
                    $offers[] = $offer;
                }
            }
            
            return [
                'success' => true,
                'offers' => $offers
            ];
            
        } catch (\Exception $e) {
            \Log::error('Personalized Offer Generation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Collect business data for AI analysis
     */
    protected function collectBusinessData($branchId)
    {
        $now = Carbon::now();
        $lastMonth = $now->copy()->subMonth();
        
        return [
            'sales_data' => [
                'total_sales' => Order::where('branch_id', $branchId)
                    ->whereBetween('created_at', [$lastMonth, $now])
                    ->sum('total_amount'),
                'total_orders' => Order::where('branch_id', $branchId)
                    ->whereBetween('created_at', [$lastMonth, $now])
                    ->count(),
                'average_order_value' => Order::where('branch_id', $branchId)
                    ->whereBetween('created_at', [$lastMonth, $now])
                    ->avg('total_amount') ?? 0,
                'top_products' => $this->getTopProducts($branchId, $lastMonth, $now),
                'slow_moving_products' => $this->getSlowMovingProducts($branchId, $lastMonth, $now),
            ],
            'customer_data' => [
                'total_customers' => User::whereHas('orders', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                })->count(),
                'new_customers' => User::whereHas('orders', function($q) use ($branchId, $lastMonth, $now) {
                    $q->where('branch_id', $branchId)
                      ->whereBetween('created_at', [$lastMonth, $now]);
                })->count(),
                'customer_segments' => $this->getCustomerSegments($branchId),
            ],
            'seasonal_data' => [
                'current_month' => $now->format('F'),
                'current_day' => $now->format('l'),
                'is_weekend' => $now->isWeekend(),
                'is_holiday' => $this->isHoliday($now),
                'weather_condition' => $this->getWeatherCondition(), // You can integrate weather API
            ],
            'inventory_data' => [
                'low_stock_items' => $this->getLowStockItems($branchId),
                'excess_stock_items' => $this->getExcessStockItems($branchId),
            ],
            'competition_data' => [
                'market_trends' => $this->getMarketTrends(),
                'competitor_offers' => $this->getCompetitorOffers(), // You can integrate competitor monitoring
            ]
        ];
    }

    /**
     * Generate offers using AI
     */
    protected function generateOffersWithAI($businessData)
    {
        $prompt = $this->buildOfferGenerationPrompt($businessData);
        
        $response = $this->openAIService->generateCompletion($prompt, [
            'temperature' => 0.8,
            'max_tokens' => 2000
        ]);
        
        return $this->parseAIResponse($response);
    }

    /**
     * Build prompt for AI offer generation
     */
    protected function buildOfferGenerationPrompt($businessData)
    {
        return "You are an expert marketing AI for a momo restaurant. Based on the following business data, generate 3-5 special offers that would be effective:

Business Data:
" . json_encode($businessData, JSON_PRETTY_PRINT) . "

Generate offers in this JSON format:
[
  {
    \"title\": \"Offer Title\",
    \"description\": \"Detailed description of the offer\",
    \"discount\": 15.00,
    \"type\": \"discount|bogo|flash|loyalty|bulk|seasonal\",
    \"code\": \"UNIQUE_CODE\",
    \"min_purchase\": 20.00,
    \"max_discount\": 50.00,
    \"valid_days\": 7,
    \"target_audience\": \"new_customers|returning_customers|all\",
    \"reasoning\": \"Why this offer makes sense based on the data\"
  }
]

Consider:
1. Current sales performance and trends
2. Customer behavior patterns
3. Seasonal factors and holidays
4. Inventory levels (promote slow-moving items, clear excess stock)
5. Competition and market conditions
6. Customer segments and their preferences

Make offers engaging, profitable, and data-driven.";
    }

    /**
     * Parse AI response into structured offer data
     */
    protected function parseAIResponse($response)
    {
        try {
            // Try to extract JSON from the response
            $jsonStart = strpos($response, '[');
            $jsonEnd = strrpos($response, ']') + 1;
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart);
                $offers = json_decode($jsonString, true);
                
                if (is_array($offers)) {
                    return $offers;
                }
            }
            
            // Fallback: return default offers if AI parsing fails
            return $this->getDefaultOffers();
            
        } catch (\Exception $e) {
            \Log::warning('AI response parsing failed, using default offers: ' . $e->getMessage());
            return $this->getDefaultOffers();
        }
    }

    /**
     * Create offer from AI data
     */
    protected function createOfferFromAI($offerData, $branchId)
    {
        try {
            // Ensure unique code
            $code = $this->generateUniqueCode($offerData['code'] ?? 'AI' . Str::random(6));
            
            $offer = new Offer();
            $offer->title = $offerData['title'];
            $offer->description = $offerData['description'];
            $offer->discount = $offerData['discount'];
            $offer->code = $code;
            $offer->min_purchase = $offerData['min_purchase'] ?? 0;
            $offer->max_discount = $offerData['max_discount'] ?? null;
            $offer->valid_from = Carbon::now();
            $offer->valid_until = Carbon::now()->addDays($offerData['valid_days'] ?? 7);
            $offer->is_active = true;
            $offer->type = $offerData['type'] ?? 'discount';
            $offer->target_audience = $offerData['target_audience'] ?? 'all';
            $offer->ai_generated = true;
            $offer->ai_reasoning = $offerData['reasoning'] ?? null;
            $offer->branch_id = $branchId;
            
            $offer->save();
            
            return $offer;
            
        } catch (\Exception $e) {
            \Log::error('Failed to create AI offer: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user behavior data for personalized offers
     */
    protected function getUserBehaviorData(User $user, $branchId)
    {
        $userOrders = Order::where('user_id', $user->id)
            ->where('branch_id', $branchId)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $favoriteProducts = $userOrders->flatMap(function($order) {
            return $order->items->pluck('product');
        })->groupBy('id')->map(function($group) {
            return $group->count();
        })->sortDesc()->take(5);
        
        $totalSpent = $userOrders->sum('total_amount');
        $orderCount = $userOrders->count();
        $lastOrderDate = $userOrders->first()?->created_at;
        $daysSinceLastOrder = $lastOrderDate ? Carbon::now()->diffInDays($lastOrderDate) : null;
        
        return [
            'user_id' => $user->id,
            'total_spent' => $totalSpent,
            'order_count' => $orderCount,
            'average_order_value' => $orderCount > 0 ? $totalSpent / $orderCount : 0,
            'favorite_products' => $favoriteProducts->keys()->toArray(),
            'days_since_last_order' => $daysSinceLastOrder,
            'customer_lifetime_value' => $totalSpent,
            'preferred_categories' => $this->getPreferredCategories($userOrders),
            'order_frequency' => $this->calculateOrderFrequency($userOrders),
            'seasonal_preferences' => $this->getSeasonalPreferences($userOrders),
        ];
    }

    /**
     * Generate personalized offers with AI
     */
    protected function generatePersonalizedOffersWithAI($userData)
    {
        $prompt = "Generate 2-3 personalized offers for a momo restaurant customer based on their behavior:

User Data:
" . json_encode($userData, JSON_PRETTY_PRINT) . "

Generate offers in JSON format:
[
  {
    \"title\": \"Personalized Offer Title\",
    \"description\": \"Personalized description\",
    \"discount\": 15.00,
    \"type\": \"discount|bogo|flash|loyalty|bulk\",
    \"code\": \"PERSONAL_CODE\",
    \"min_purchase\": 20.00,
    \"max_discount\": 30.00,
    \"valid_days\": 3,
    \"reasoning\": \"Why this offer is perfect for this customer\"
  }
]

Consider:
1. Customer's favorite products and categories
2. Order frequency and recency
3. Average order value
4. Seasonal preferences
5. Customer lifetime value
6. Days since last order (re-engagement if needed)

Make offers highly personalized and relevant to this specific customer.";

        $response = $this->openAIService->generateCompletion($prompt, [
            'temperature' => 0.9,
            'max_tokens' => 1500
        ]);
        
        return $this->parseAIResponse($response);
    }

    /**
     * Create personalized offer for specific user
     */
    protected function createPersonalizedOffer($offerData, User $user, $branchId)
    {
        try {
            $code = $this->generateUniqueCode($offerData['code'] ?? 'PERSONAL' . Str::random(4));
            
            $offer = new Offer();
            $offer->title = $offerData['title'];
            $offer->description = $offerData['description'];
            $offer->discount = $offerData['discount'];
            $offer->code = $code;
            $offer->min_purchase = $offerData['min_purchase'] ?? 0;
            $offer->max_discount = $offerData['max_discount'] ?? null;
            $offer->valid_from = Carbon::now();
            $offer->valid_until = Carbon::now()->addDays($offerData['valid_days'] ?? 3);
            $offer->is_active = true;
            $offer->type = $offerData['type'] ?? 'discount';
            $offer->target_audience = 'personalized';
            $offer->ai_generated = true;
            $offer->ai_reasoning = $offerData['reasoning'] ?? null;
            $offer->branch_id = $branchId;
            $offer->user_id = $user->id; // Link to specific user
            
            $offer->save();
            
            return $offer;
            
        } catch (\Exception $e) {
            \Log::error('Failed to create personalized offer: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate unique offer code
     */
    protected function generateUniqueCode($baseCode)
    {
        $code = strtoupper($baseCode);
        $counter = 1;
        
        while (Offer::where('code', $code)->exists()) {
            $code = strtoupper($baseCode) . $counter;
            $counter++;
        }
        
        return $code;
    }

    /**
     * Get default offers as fallback
     */
    protected function getDefaultOffers()
    {
        return [
            [
                'title' => 'AI Generated Flash Sale',
                'description' => 'Limited time offer generated by AI based on current business data',
                'discount' => 20.00,
                'type' => 'flash',
                'code' => 'AI' . Str::random(6),
                'min_purchase' => 25.00,
                'max_discount' => 40.00,
                'valid_days' => 2,
                'target_audience' => 'all',
                'reasoning' => 'AI-generated offer to boost sales during current period'
            ],
            [
                'title' => 'Smart Loyalty Reward',
                'description' => 'Special reward for our valued customers',
                'discount' => 10.00,
                'type' => 'loyalty',
                'code' => 'AI' . Str::random(6),
                'min_purchase' => 15.00,
                'max_discount' => 20.00,
                'valid_days' => 7,
                'target_audience' => 'returning_customers',
                'reasoning' => 'AI-generated loyalty offer to increase customer retention'
            ]
        ];
    }

    // Helper methods for data collection
    protected function getTopProducts($branchId, $startDate, $endDate)
    {
        return OrderItem::whereHas('order', function($q) use ($branchId, $startDate, $endDate) {
            $q->where('branch_id', $branchId)
              ->whereBetween('created_at', [$startDate, $endDate]);
        })
        ->with('product')
        ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
        ->groupBy('product_id')
        ->orderByDesc('total_sold')
        ->limit(10)
        ->get()
        ->map(function($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product->name ?? 'Unknown',
                'total_sold' => $item->total_sold
            ];
        });
    }

    protected function getSlowMovingProducts($branchId, $startDate, $endDate)
    {
        return Product::where('branch_id', $branchId)
            ->where('stock', '>', 0)
            ->whereDoesntHave('orderItems.order', function($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock
                ];
            });
    }

    protected function getCustomerSegments($branchId)
    {
        return CustomerSegment::where('branch_id', $branchId)
            ->withCount('customers')
            ->get()
            ->map(function($segment) {
                return [
                    'id' => $segment->id,
                    'name' => $segment->name,
                    'customer_count' => $segment->customers_count
                ];
            });
    }

    protected function isHoliday($date)
    {
        // Add holiday detection logic
        $holidays = [
            '12-25', // Christmas
            '01-01', // New Year
            '07-04', // Independence Day (US)
            // Add more holidays as needed
        ];
        
        return in_array($date->format('m-d'), $holidays);
    }

    protected function getWeatherCondition()
    {
        // You can integrate with weather API here
        return 'moderate'; // Placeholder
    }

    protected function getLowStockItems($branchId)
    {
        return Product::where('branch_id', $branchId)
            ->where('stock', '<', 10)
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock
                ];
            });
    }

    protected function getExcessStockItems($branchId)
    {
        return Product::where('branch_id', $branchId)
            ->where('stock', '>', 50)
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock
                ];
            });
    }

    protected function getMarketTrends()
    {
        // You can integrate with market analysis APIs
        return [
            'trend' => 'growing',
            'competition_level' => 'moderate',
            'seasonal_demand' => 'high'
        ];
    }

    protected function getCompetitorOffers()
    {
        // You can integrate competitor monitoring
        return [
            'average_discount' => 15,
            'common_offers' => ['lunch_specials', 'weekend_deals']
        ];
    }

    protected function getPreferredCategories($userOrders)
    {
        return $userOrders->flatMap(function($order) {
            return $order->items->pluck('product.category');
        })->groupBy(function($category) {
            return $category;
        })->map(function($group) {
            return $group->count();
        })->sortDesc()->take(3)->keys()->toArray();
    }

    protected function calculateOrderFrequency($userOrders)
    {
        if ($userOrders->count() < 2) {
            return 'new_customer';
        }
        
        $firstOrder = $userOrders->last();
        $lastOrder = $userOrders->first();
        $daysBetween = $firstOrder->created_at->diffInDays($lastOrder->created_at);
        $orderCount = $userOrders->count();
        
        $frequency = $daysBetween / $orderCount;
        
        if ($frequency <= 7) {
            return 'high_frequency';
        } elseif ($frequency <= 30) {
            return 'medium_frequency';
        } else {
            return 'low_frequency';
        }
    }

    protected function getSeasonalPreferences($userOrders)
    {
        return $userOrders->groupBy(function($order) {
            return $order->created_at->format('F');
        })->map(function($orders) {
            return $orders->count();
        })->sortDesc()->take(3)->keys()->toArray();
    }
} 