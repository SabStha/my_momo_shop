<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductRating;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        // Log the incoming request for debugging
        Log::info('Review submission attempt', [
            'user_id' => auth()->id(),
            'data' => $request->all(),
            'is_ajax' => $request->ajax(),
            'wants_json' => $request->wantsJson(),
            'headers' => $request->headers->all()
        ]);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        try {
            $user = auth()->user();
            
            // Create the review
            $review = ProductRating::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'rating' => $request->rating,
                'review' => $request->review,
            ]);

            // Generate AI-powered personalized gift
            $gift = $this->generateAIGift($review, $user);

            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                Log::info('Sending AJAX response', [
                    'success' => true,
                    'review_id' => $review->id,
                    'gift' => $gift ? $gift->id : null
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Thank you for your review! Your feedback helps us improve our service.',
                    'review_id' => $review->id,
                    'gift' => $gift ? [
                        'id' => $gift->id,
                        'title' => $gift->title,
                        'description' => $gift->description,
                        'code' => $gift->code,
                        'discount' => $gift->discount,
                        'valid_until' => $gift->valid_until->format('Y-m-d H:i:s')
                    ] : null
                ]);
            }

            return redirect()->back()->with('success', 'Review submitted successfully!');

        } catch (\Exception $e) {
            Log::error('Review submission failed: ' . $e->getMessage());
            
            // Check if it's an AJAX request
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                Log::error('Sending AJAX error response', [
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Sorry, there was an error submitting your review. Please try again.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to submit review. Please try again.');
        }
    }

    /**
     * Generate AI-powered personalized gift based on review and customer data
     */
    protected function generateAIGift(ProductRating $review, User $user)
    {
        try {
            // Check if review is good enough for a gift (4-5 stars)
            if ($review->rating < 4) {
                Log::info('Review rating too low for gift', [
                    'user_id' => $user->id,
                    'review_id' => $review->id,
                    'rating' => $review->rating
                ]);
                return null; // No gift for low ratings
            }

            // Check if user has already received a gift recently (within 30 days)
            $recentGift = \App\Models\Offer::where('user_id', $user->id)
                ->where('ai_generated', true)
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->first();

            if ($recentGift) {
                Log::info('User already received gift recently', [
                    'user_id' => $user->id,
                    'last_gift_id' => $recentGift->id,
                    'last_gift_date' => $recentGift->created_at
                ]);
                return null; // No gift if received recently
            }

            // Check if user has submitted too many reviews recently (prevent spam)
            $recentReviews = ProductRating::where('user_id', $user->id)
                ->where('created_at', '>=', Carbon::now()->subDays(7))
                ->count();

            if ($recentReviews > 3) {
                Log::info('User submitted too many reviews recently', [
                    'user_id' => $user->id,
                    'recent_reviews_count' => $recentReviews
                ]);
                return null; // No gift if too many reviews
            }

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
            $totalSpent = $userOrders->sum('total');
            $orderCount = $userOrders->count();
            $reviewCount = $userReviews->count();
            $averageRating = $userReviews->avg('rating');
            
            // Determine customer segment and loyalty level
            $customerSegment = $this->determineCustomerSegment($totalSpent, $orderCount, $reviewCount);
            $loyaltyLevel = $this->calculateLoyaltyLevel($totalSpent, $orderCount, $reviewCount);
            
            // Analyze review content
            $reviewAnalysis = $this->analyzeReviewContent($review);
            
            // Generate personalized gift based on analysis
            $giftData = $this->generateGiftRecommendation($customerSegment, $loyaltyLevel, $reviewAnalysis, $review);
            
            // Create the gift as an offer
            $gift = $this->createGiftOffer($giftData, $user, $review);
            
            Log::info('AI Gift Generated', [
                'user_id' => $user->id,
                'review_id' => $review->id,
                'customer_segment' => $customerSegment,
                'loyalty_level' => $loyaltyLevel,
                'gift_data' => $giftData,
                'gift_id' => $gift ? $gift->id : null,
                'review_rating' => $review->rating,
                'review_quality' => $reviewAnalysis['is_detailed'] ? 'detailed' : 'simple'
            ]);
            
            return $gift;
            
        } catch (\Exception $e) {
            Log::error('AI Gift Generation Failed: ' . $e->getMessage());
            return null; // Don't give fallback gift for failed AI generation
        }
    }

    /**
     * Determine customer segment
     */
    protected function determineCustomerSegment($totalSpent, $orderCount, $reviewCount)
    {
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
     * Calculate loyalty level
     */
    protected function calculateLoyaltyLevel($totalSpent, $orderCount, $reviewCount)
    {
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
     * Analyze review content
     */
    protected function analyzeReviewContent(ProductRating $review)
    {
        $reviewText = $review->review ?? '';
        $rating = $review->rating;
        
        // Simple sentiment analysis
        $sentimentScore = $rating >= 4 ? 'positive' : ($rating >= 3 ? 'neutral' : 'negative');
        
        // Determine if review is detailed
        $isDetailed = strlen($reviewText) > 50;
        
        // Extract keywords
        $keywords = $this->extractKeywords($reviewText);
        
        return [
            'sentiment_score' => $sentimentScore,
            'is_detailed' => $isDetailed,
            'keywords' => $keywords,
            'rating' => $rating,
            'review_length' => strlen($reviewText)
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
        
        return array_slice($foundKeywords, 0, 5);
    }

    /**
     * Generate gift recommendation based on customer data
     */
    protected function generateGiftRecommendation($customerSegment, $loyaltyLevel, $reviewAnalysis, $review)
    {
        $baseDiscount = 10;
        $validDays = 30;
        
        // Adjust based on customer segment
        switch ($customerSegment) {
            case 'VIP':
                $baseDiscount = 25;
                $validDays = 60;
                break;
            case 'Loyal':
                $baseDiscount = 20;
                $validDays = 45;
                break;
            case 'Regular':
                $baseDiscount = 15;
                $validDays = 35;
                break;
            default:
                $baseDiscount = 10;
                $validDays = 30;
        }
        
        // Adjust based on review quality (only for 4-5 star reviews)
        if ($review->rating >= 5) {
            $baseDiscount += 8; // Bonus for perfect rating
        } elseif ($review->rating >= 4) {
            $baseDiscount += 5; // Bonus for good rating
        }
        
        if ($reviewAnalysis['is_detailed']) {
            $baseDiscount += 5; // Bonus for detailed review
        }
        
        // Additional bonus for first-time reviewers
        $userReviewCount = ProductRating::where('user_id', $review->user_id)->count();
        if ($userReviewCount === 1) {
            $baseDiscount += 3; // First review bonus
        }
        
        // Cap the maximum discount
        $baseDiscount = min($baseDiscount, 50); // Maximum 50% discount
        
        // Generate gift title and description
        $giftData = $this->generateGiftContent($customerSegment, $loyaltyLevel, $reviewAnalysis, $review);
        
        return [
            'title' => $giftData['title'],
            'description' => $giftData['description'],
            'discount' => $baseDiscount,
            'valid_days' => $validDays,
            'reasoning' => $giftData['reasoning']
        ];
    }

    /**
     * Generate gift content based on customer profile
     */
    protected function generateGiftContent($customerSegment, $loyaltyLevel, $reviewAnalysis, $review)
    {
        $giftTemplates = [
            'VIP' => [
                'title' => 'VIP Review Appreciation',
                'description' => 'As our valued VIP customer, we\'re delighted to offer you this exclusive discount for sharing your feedback!',
                'reasoning' => 'VIP customer with quality review - premium reward'
            ],
            'Loyal' => [
                'title' => 'Loyal Customer Reward',
                'description' => 'Thank you for your continued loyalty and detailed feedback! Enjoy this special discount.',
                'reasoning' => 'Loyal customer with quality review - enhanced reward'
            ],
            'Regular' => [
                'title' => 'Review Appreciation Gift',
                'description' => 'Your feedback helps us improve! Here\'s a special discount as a token of our appreciation.',
                'reasoning' => 'Regular customer with quality review - standard reward'
            ],
            'New' => [
                'title' => 'Welcome Review Gift',
                'description' => 'Thank you for choosing us and sharing your first review! Enjoy this welcome discount.',
                'reasoning' => 'New customer with quality review - welcome reward'
            ]
        ];
        
        $template = $giftTemplates[$customerSegment] ?? $giftTemplates['New'];
        
        // Customize based on review analysis
        if ($review->rating >= 5) {
            $template['description'] .= ' We\'re thrilled you had a perfect experience!';
            $template['reasoning'] .= ' - Perfect 5-star rating bonus';
        } elseif ($review->rating >= 4) {
            $template['description'] .= ' We\'re glad you had a great experience!';
            $template['reasoning'] .= ' - Good 4+ star rating bonus';
        }
        
        if ($reviewAnalysis['is_detailed']) {
            $template['description'] .= ' Your detailed feedback is especially valuable to us!';
            $template['reasoning'] .= ' - Detailed review bonus';
        }
        
        // First review bonus
        $userReviewCount = ProductRating::where('user_id', $review->user_id)->count();
        if ($userReviewCount === 1) {
            $template['description'] .= ' Welcome to our review community!';
            $template['reasoning'] .= ' - First review bonus';
        }
        
        return $template;
    }

    /**
     * Create gift offer
     */
    protected function createGiftOffer($giftData, User $user, ProductRating $review)
    {
        try {
            $code = $this->generateUniqueGiftCode('GIFT' . Str::random(6));
            
            $offer = new \App\Models\Offer();
            $offer->title = $giftData['title'];
            $offer->description = $giftData['description'];
            $offer->discount = $giftData['discount'];
            $offer->code = $code;
            $offer->min_purchase = 0.00;
            $offer->max_discount = $giftData['discount'] * 2; // Allow up to 2x the discount
            $offer->valid_from = Carbon::now();
            $offer->valid_until = Carbon::now()->addDays($giftData['valid_days']);
            $offer->is_active = true;
            $offer->type = 'discount';
            $offer->target_audience = 'personalized';
            $offer->ai_generated = true;
            $offer->ai_reasoning = $giftData['reasoning'];
            $offer->user_id = $user->id;
            $offer->branch_id = 1;
            
            $offer->save();
            
            return $offer;
            
        } catch (\Exception $e) {
            Log::error('Failed to create gift offer: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate unique gift code
     */
    protected function generateUniqueGiftCode($baseCode)
    {
        $code = strtoupper($baseCode);
        $counter = 1;
        
        while (\App\Models\Offer::where('code', $code)->exists()) {
            $code = strtoupper($baseCode) . $counter;
            $counter++;
        }
        
        return $code;
    }
} 