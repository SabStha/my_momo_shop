<?php

namespace App\Services;

use App\Models\User;
use App\Models\ProductRating;
use App\Models\Order;
use App\Models\Product;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AIGiftService
{
    protected $openAIService;
    protected $customerAnalyticsService;
    protected $aiOfferService;

    public function __construct(
        OpenAIService $openAIService,
        CustomerAnalyticsService $customerAnalyticsService,
        AIOfferService $aiOfferService
    ) {
        $this->openAIService = $openAIService;
        $this->customerAnalyticsService = $customerAnalyticsService;
        $this->aiOfferService = $aiOfferService;
    }

    /**
     * Generate personalized gift based on review and customer data
     */
    public function generatePersonalizedGift(ProductRating $review, User $user)
    {
        try {
            // Collect comprehensive data for AI analysis
            $giftData = $this->collectGiftData($review, $user);
            
            // Generate gift recommendation using AI
            $giftRecommendation = $this->generateGiftWithAI($giftData);
            
            // Create and return the gift
            $gift = $this->createGiftFromRecommendation($giftRecommendation, $user, $review);
            
            return [
                'success' => true,
                'gift' => $gift,
                'reasoning' => $giftRecommendation['reasoning'] ?? 'AI-generated personalized gift based on your review and preferences'
            ];
            
        } catch (\Exception $e) {
            \Log::error('AI Gift Generation Failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fallback_gift' => $this->generateFallbackGift($user)
            ];
        }
    }

    /**
     * Collect comprehensive data for gift analysis
     */
    protected function collectGiftData(ProductRating $review, User $user)
    {
        // Get user's order history and preferences
        $userOrders = Order::where('user_id', $user->id)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get user's review history
        $userReviews = ProductRating::where('user_id', $user->id)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate customer metrics
        $customerMetrics = $this->calculateCustomerMetrics($userOrders, $userReviews);
        
        // Analyze review sentiment and content
        $reviewAnalysis = $this->analyzeReviewContent($review);
        
        // Get seasonal and contextual data
        $contextualData = $this->getContextualData();

        return [
            'user_profile' => [
                'user_id' => $user->id,
                'total_orders' => $userOrders->count(),
                'total_spent' => $userOrders->sum('total'),
                'average_order_value' => $userOrders->avg('total'),
                'favorite_categories' => $this->getFavoriteCategories($userOrders),
                'favorite_products' => $this->getFavoriteProducts($userOrders),
                'review_count' => $userReviews->count(),
                'average_rating' => $userReviews->avg('rating'),
                'customer_segment' => $this->determineCustomerSegment($customerMetrics),
                'loyalty_level' => $this->calculateLoyaltyLevel($customerMetrics),
                'days_since_last_order' => $this->getDaysSinceLastOrder($userOrders),
                'order_frequency' => $this->calculateOrderFrequency($userOrders),
            ],
            'review_data' => [
                'rating' => $review->rating,
                'review_text' => $review->review,
                'product_id' => $review->product_id,
                'product_name' => $review->product->name ?? 'Unknown Product',
                'sentiment_score' => $reviewAnalysis['sentiment_score'],
                'keywords' => $reviewAnalysis['keywords'],
                'review_length' => strlen($review->review ?? ''),
                'is_detailed_review' => $reviewAnalysis['is_detailed'],
                'review_tone' => $reviewAnalysis['tone'],
                'specific_feedback' => $reviewAnalysis['specific_feedback'],
            ],
            'contextual_data' => [
                'current_season' => $contextualData['season'],
                'current_month' => Carbon::now()->format('F'),
                'is_holiday_season' => $contextualData['is_holiday'],
                'weather_condition' => $contextualData['weather'],
                'time_of_day' => Carbon::now()->format('H:i'),
                'day_of_week' => Carbon::now()->format('l'),
            ],
            'business_data' => [
                'total_customers' => User::count(),
                'average_rating' => ProductRating::avg('rating'),
                'popular_products' => $this->getPopularProducts(),
                'current_offers' => $this->getCurrentOffers(),
                'inventory_status' => $this->getInventoryStatus(),
            ]
        ];
    }

    /**
     * Generate gift recommendation using AI
     */
    protected function generateGiftWithAI($giftData)
    {
        $prompt = $this->buildGiftGenerationPrompt($giftData);
        
        $response = $this->openAIService->generateCompletion($prompt, [
            'temperature' => 0.8,
            'max_tokens' => 1500
        ]);
        
        return $this->parseGiftResponse($response);
    }

    /**
     * Build prompt for AI gift generation
     */
    protected function buildGiftGenerationPrompt($giftData)
    {
        return "You are an expert gift recommendation AI for a momo restaurant. Based on the following customer data and review, generate a personalized gift recommendation:

Customer Profile:
- Total Orders: {$giftData['user_profile']['total_orders']}
- Total Spent: Rs {$giftData['user_profile']['total_spent']}
- Average Order Value: Rs {$giftData['user_profile']['average_order_value']}
- Customer Segment: {$giftData['user_profile']['customer_segment']}
- Loyalty Level: {$giftData['user_profile']['loyalty_level']}
- Favorite Categories: " . implode(', ', $giftData['user_profile']['favorite_categories']) . "
- Review Count: {$giftData['user_profile']['review_count']}
- Average Rating: {$giftData['user_profile']['average_rating']}

Review Data:
- Rating: {$giftData['review_data']['rating']}/5
- Review Text: \"{$giftData['review_data']['review_text']}\"
- Product: {$giftData['review_data']['product_name']}
- Sentiment Score: {$giftData['review_data']['sentiment_score']}
- Keywords: " . implode(', ', $giftData['review_data']['keywords']) . "
- Review Tone: {$giftData['review_data']['review_tone']}
- Is Detailed Review: " . ($giftData['review_data']['is_detailed_review'] ? 'Yes' : 'No') . "

Contextual Data:
- Season: {$giftData['contextual_data']['current_season']}
- Month: {$giftData['contextual_data']['current_month']}
- Holiday Season: " . ($giftData['contextual_data']['is_holiday_season'] ? 'Yes' : 'No') . "

Generate a gift recommendation in this JSON format:
{
  \"gift_type\": \"discount|free_item|loyalty_points|combo_offer|vip_access|personalized_coupon\",
  \"title\": \"Gift Title\",
  \"description\": \"Detailed description of the gift\",
  \"value\": 15.00,
  \"value_type\": \"percentage|fixed_amount|points|items\",
  \"code\": \"GIFT_CODE\",
  \"valid_days\": 30,
  \"min_purchase\": 0.00,
  \"max_discount\": 50.00,
  \"reasoning\": \"Why this gift is perfect for this customer based on their profile and review\",
  \"personalization_factors\": [\"factor1\", \"factor2\"],
  \"urgency_level\": \"high|medium|low\",
  \"estimated_impact\": \"high|medium|low\"
}

Consider:
1. Customer's loyalty level and spending patterns
2. Review sentiment and content analysis
3. Favorite products and categories
4. Seasonal appropriateness
5. Review quality (detailed reviews deserve better gifts)
6. Customer segment and lifetime value
7. Current business context and inventory

Make the gift highly personalized and relevant to this specific customer.";
    }

    /**
     * Parse AI response into structured gift data
     */
    protected function parseGiftResponse($response)
    {
        try {
            // Try to extract JSON from the response
            $jsonStart = strpos($response, '{');
            $jsonEnd = strrpos($response, '}');
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart + 1);
                $giftData = json_decode($jsonString, true);
                
                if ($giftData && is_array($giftData)) {
                    return $giftData;
                }
            }
            
            // Fallback to default gift structure
            return $this->getDefaultGiftStructure();
            
        } catch (\Exception $e) {
            \Log::error('Failed to parse AI gift response: ' . $e->getMessage());
            return $this->getDefaultGiftStructure();
        }
    }

    /**
     * Create gift from AI recommendation
     */
    protected function createGiftFromRecommendation($giftData, User $user, ProductRating $review)
    {
        try {
            // Generate unique code
            $code = $this->generateUniqueGiftCode($giftData['code'] ?? 'GIFT' . Str::random(6));
            
            // Create the gift as an offer
            $offer = new Offer();
            $offer->title = $giftData['title'] ?? 'AI-Generated Review Gift';
            $offer->description = $giftData['description'] ?? 'Special gift for leaving a review!';
            $offer->discount = $giftData['value'] ?? 10.00;
            $offer->code = $code;
            $offer->min_purchase = $giftData['min_purchase'] ?? 0.00;
            $offer->max_discount = $giftData['max_discount'] ?? null;
            $offer->valid_from = Carbon::now();
            $offer->valid_until = Carbon::now()->addDays($giftData['valid_days'] ?? 30);
            $offer->is_active = true;
            $offer->type = $giftData['gift_type'] ?? 'discount';
            $offer->target_audience = 'personalized';
            $offer->ai_generated = true;
            $offer->ai_reasoning = $giftData['reasoning'] ?? 'AI-generated personalized gift';
            $offer->user_id = $user->id; // Link to specific user
            $offer->branch_id = 1; // Default branch
            
            $offer->save();
            
            // Log the gift creation
            \Log::info('AI Gift Created', [
                'user_id' => $user->id,
                'review_id' => $review->id,
                'gift_data' => $giftData,
                'offer_id' => $offer->id
            ]);
            
            return $offer;
            
        } catch (\Exception $e) {
            \Log::error('Failed to create AI gift: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate fallback gift when AI fails
     */
    protected function generateFallbackGift(User $user)
    {
        $fallbackGifts = [
            [
                'title' => 'Thank You Discount',
                'description' => 'Special discount for sharing your feedback!',
                'discount' => 10.00,
                'code' => 'THANKYOU' . Str::random(4),
                'valid_days' => 30
            ],
            [
                'title' => 'Review Reward',
                'description' => 'Your feedback is valuable! Enjoy this special offer.',
                'discount' => 15.00,
                'code' => 'REVIEW' . Str::random(4),
                'valid_days' => 21
            ],
            [
                'title' => 'Customer Appreciation',
                'description' => 'We appreciate your feedback! Here\'s a special treat.',
                'discount' => 12.00,
                'code' => 'APPRECIATE' . Str::random(4),
                'valid_days' => 25
            ]
        ];
        
        $selectedGift = $fallbackGifts[array_rand($fallbackGifts)];
        
        try {
            $offer = new Offer();
            $offer->title = $selectedGift['title'];
            $offer->description = $selectedGift['description'];
            $offer->discount = $selectedGift['discount'];
            $offer->code = $this->generateUniqueGiftCode($selectedGift['code']);
            $offer->min_purchase = 0.00;
            $offer->valid_from = Carbon::now();
            $offer->valid_until = Carbon::now()->addDays($selectedGift['valid_days']);
            $offer->is_active = true;
            $offer->type = 'discount';
            $offer->target_audience = 'personalized';
            $offer->ai_generated = false;
            $offer->user_id = $user->id;
            $offer->branch_id = 1;
            
            $offer->save();
            
            return $offer;
            
        } catch (\Exception $e) {
            \Log::error('Failed to create fallback gift: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate customer metrics
     */
    protected function calculateCustomerMetrics($userOrders, $userReviews)
    {
        $totalSpent = $userOrders->sum('total');
        $orderCount = $userOrders->count();
        $reviewCount = $userReviews->count();
        $averageRating = $userReviews->avg('rating');
        
        return [
            'total_spent' => $totalSpent,
            'order_count' => $orderCount,
            'average_order_value' => $orderCount > 0 ? $totalSpent / $orderCount : 0,
            'review_count' => $reviewCount,
            'average_rating' => $averageRating,
            'engagement_score' => $this->calculateEngagementScore($userOrders, $userReviews)
        ];
    }

    /**
     * Analyze review content
     */
    protected function analyzeReviewContent(ProductRating $review)
    {
        $reviewText = $review->review ?? '';
        $rating = $review->rating;
        
        // Simple sentiment analysis based on rating and text length
        $sentimentScore = $rating >= 4 ? 'positive' : ($rating >= 3 ? 'neutral' : 'negative');
        
        // Extract keywords (simple approach)
        $keywords = $this->extractKeywords($reviewText);
        
        // Determine if review is detailed
        $isDetailed = strlen($reviewText) > 50;
        
        // Determine tone
        $tone = $this->determineTone($reviewText, $rating);
        
        // Extract specific feedback
        $specificFeedback = $this->extractSpecificFeedback($reviewText);
        
        return [
            'sentiment_score' => $sentimentScore,
            'keywords' => $keywords,
            'is_detailed' => $isDetailed,
            'tone' => $tone,
            'specific_feedback' => $specificFeedback
        ];
    }

    /**
     * Extract keywords from review text
     */
    protected function extractKeywords($text)
    {
        if (empty($text)) return [];
        
        $commonKeywords = [
            'delicious', 'tasty', 'amazing', 'great', 'good', 'excellent', 'wonderful',
            'fast', 'quick', 'slow', 'hot', 'cold', 'fresh', 'quality', 'service',
            'delivery', 'staff', 'friendly', 'clean', 'value', 'price', 'portion',
            'spicy', 'mild', 'sweet', 'sour', 'crunchy', 'soft', 'juicy', 'dry'
        ];
        
        $foundKeywords = [];
        $lowerText = strtolower($text);
        
        foreach ($commonKeywords as $keyword) {
            if (strpos($lowerText, $keyword) !== false) {
                $foundKeywords[] = $keyword;
            }
        }
        
        return array_slice($foundKeywords, 0, 5); // Limit to 5 keywords
    }

    /**
     * Determine review tone
     */
    protected function determineTone($text, $rating)
    {
        if (empty($text)) return 'neutral';
        
        $positiveWords = ['love', 'amazing', 'excellent', 'wonderful', 'fantastic', 'perfect', 'great', 'delicious'];
        $negativeWords = ['hate', 'terrible', 'awful', 'disgusting', 'bad', 'poor', 'worst', 'disappointed'];
        
        $lowerText = strtolower($text);
        $positiveCount = 0;
        $negativeCount = 0;
        
        foreach ($positiveWords as $word) {
            if (strpos($lowerText, $word) !== false) $positiveCount++;
        }
        
        foreach ($negativeWords as $word) {
            if (strpos($lowerText, $word) !== false) $negativeCount++;
        }
        
        if ($positiveCount > $negativeCount) return 'positive';
        if ($negativeCount > $positiveCount) return 'negative';
        return 'neutral';
    }

    /**
     * Extract specific feedback from review
     */
    protected function extractSpecificFeedback($text)
    {
        if (empty($text)) return [];
        
        $feedback = [];
        
        // Look for specific mentions
        if (strpos(strtolower($text), 'delivery') !== false) $feedback[] = 'delivery';
        if (strpos(strtolower($text), 'service') !== false) $feedback[] = 'service';
        if (strpos(strtolower($text), 'quality') !== false) $feedback[] = 'quality';
        if (strpos(strtolower($text), 'price') !== false) $feedback[] = 'pricing';
        if (strpos(strtolower($text), 'taste') !== false) $feedback[] = 'taste';
        
        return $feedback;
    }

    /**
     * Get favorite categories
     */
    protected function getFavoriteCategories($userOrders)
    {
        return $userOrders->flatMap(function($order) {
            return $order->items->pluck('product.category');
        })->filter()->groupBy(function($category) {
            return $category;
        })->map(function($group) {
            return $group->count();
        })->sortDesc()->keys()->take(3)->toArray();
    }

    /**
     * Get favorite products
     */
    protected function getFavoriteProducts($userOrders)
    {
        return $userOrders->flatMap(function($order) {
            return $order->items->pluck('product.name');
        })->filter()->groupBy(function($name) {
            return $name;
        })->map(function($group) {
            return $group->count();
        })->sortDesc()->keys()->take(3)->toArray();
    }

    /**
     * Determine customer segment
     */
    protected function determineCustomerSegment($metrics)
    {
        $totalSpent = $metrics['total_spent'];
        $orderCount = $metrics['order_count'];
        $engagementScore = $metrics['engagement_score'];
        
        if ($totalSpent >= 1000 && $orderCount >= 5 && $engagementScore >= 0.7) {
            return 'VIP';
        } elseif ($totalSpent >= 500 && $orderCount >= 3 && $engagementScore >= 0.5) {
            return 'Loyal';
        } elseif ($totalSpent >= 100 && $orderCount >= 2) {
            return 'Regular';
        } else {
            return 'New';
        }
    }

    /**
     * Calculate loyalty level
     */
    protected function calculateLoyaltyLevel($metrics)
    {
        $totalSpent = $metrics['total_spent'];
        $orderCount = $metrics['order_count'];
        $reviewCount = $metrics['review_count'];
        
        if ($totalSpent >= 1000 && $orderCount >= 5 && $reviewCount >= 2) {
            return 'VIP';
        } elseif ($totalSpent >= 500 && $orderCount >= 3 && $reviewCount >= 1) {
            return 'Loyal';
        } elseif ($totalSpent >= 100 && $orderCount >= 2) {
            return 'Regular';
        } else {
            return 'New';
        }
    }

    /**
     * Calculate engagement score
     */
    protected function calculateEngagementScore($userOrders, $userReviews)
    {
        $orderCount = $userOrders->count();
        $reviewCount = $userReviews->count();
        $lastOrderDate = $userOrders->first()?->created_at;
        $daysSinceLastOrder = $lastOrderDate ? Carbon::now()->diffInDays($lastOrderDate) : 999;
        
        $orderScore = min($orderCount / 5, 1); // Max score at 5 orders
        $reviewScore = min($reviewCount / 3, 1); // Max score at 3 reviews
        $recencyScore = max(0, 1 - ($daysSinceLastOrder / 90)); // Decay over 90 days
        
        return ($orderScore * 0.4) + ($reviewScore * 0.3) + ($recencyScore * 0.3);
    }

    /**
     * Get days since last order
     */
    protected function getDaysSinceLastOrder($userOrders)
    {
        $lastOrder = $userOrders->first();
        return $lastOrder ? Carbon::now()->diffInDays($lastOrder->created_at) : 999;
    }

    /**
     * Calculate order frequency
     */
    protected function calculateOrderFrequency($userOrders)
    {
        if ($userOrders->count() < 2) return 0;
        
        $firstOrder = $userOrders->last();
        $lastOrder = $userOrders->first();
        $totalDays = Carbon::parse($firstOrder->created_at)->diffInDays($lastOrder->created_at);
        
        return $totalDays > 0 ? $userOrders->count() / ($totalDays / 30) : 0; // Orders per month
    }

    /**
     * Get contextual data
     */
    protected function getContextualData()
    {
        $month = Carbon::now()->month;
        
        $seasons = [
            12 => 'winter', 1 => 'winter', 2 => 'winter',
            3 => 'spring', 4 => 'spring', 5 => 'spring',
            6 => 'summer', 7 => 'summer', 8 => 'summer',
            9 => 'autumn', 10 => 'autumn', 11 => 'autumn'
        ];
        
        $holidays = [12, 1, 2, 6, 7, 8]; // Winter and summer months
        
        return [
            'season' => $seasons[$month] ?? 'unknown',
            'is_holiday' => in_array($month, $holidays),
            'weather' => 'moderate' // Placeholder
        ];
    }

    /**
     * Get popular products
     */
    protected function getPopularProducts()
    {
        return Product::select('name', 'category')
            ->withCount('ratings')
            ->orderByDesc('ratings_count')
            ->limit(5)
            ->get()
            ->map(function($product) {
                return [
                    'name' => $product->name,
                    'category' => $product->category
                ];
            })
            ->toArray();
    }

    /**
     * Get current offers
     */
    protected function getCurrentOffers()
    {
        return Offer::where('is_active', true)
            ->where('valid_until', '>', Carbon::now())
            ->count();
    }

    /**
     * Get inventory status
     */
    protected function getInventoryStatus()
    {
        return [
            'low_stock' => Product::where('stock', '<', 10)->count(),
            'out_of_stock' => Product::where('stock', 0)->count(),
            'total_products' => Product::count()
        ];
    }

    /**
     * Generate unique gift code
     */
    protected function generateUniqueGiftCode($baseCode)
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
     * Get default gift structure
     */
    protected function getDefaultGiftStructure()
    {
        return [
            'gift_type' => 'discount',
            'title' => 'Review Appreciation Gift',
            'description' => 'Thank you for sharing your feedback! Enjoy this special discount.',
            'value' => 10.00,
            'value_type' => 'percentage',
            'code' => 'GIFT' . Str::random(6),
            'valid_days' => 30,
            'min_purchase' => 0.00,
            'max_discount' => 30.00,
            'reasoning' => 'Default gift for review submission',
            'personalization_factors' => ['review_submission'],
            'urgency_level' => 'medium',
            'estimated_impact' => 'medium'
        ];
    }
}
