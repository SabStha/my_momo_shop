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
use Illuminate\Support\Facades\Log;

class AIPopupService
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
     * Determine if and what popup should be shown to user
     */
    public function shouldShowPopup(User $user = null, $context = 'homepage')
    {
        try {
            // Check frequency limits first
            if (!$this->shouldShowPopupBasedOnFrequency($user)) {
                return ['show_popup' => false, 'reason' => 'frequency_limit'];
            }
            
            // Check if popup was already shown in this session
            if ($this->wasPopupShownInSession()) {
                return ['show_popup' => false, 'reason' => 'already_shown'];
            }
            
            // Collect user and business data
            $data = $this->collectPopupDecisionData($user, $context);
            
            // Use AI to decide if popup should be shown
            $decision = $this->getAIPopupDecision($data);
            
            // Fallback: Show popup occasionally even if AI says no (20% chance)
            if (!$decision['should_show'] && rand(1, 100) <= 20) {
                $decision['should_show'] = true;
                $decision['reasoning'] = 'Fallback popup to engage user';
            }
            
            if ($decision['should_show']) {
                // Generate or select appropriate offer
                $offer = $this->getOptimalOffer($user, $decision, $context);
                
                // Mark popup as shown in session
                $this->markPopupShownInSession();
                
                return [
                    'show_popup' => true,
                    'offer' => $offer,
                    'timing' => $decision['timing'],
                    'urgency' => $decision['urgency'],
                    'reasoning' => $decision['reasoning']
                ];
            }
            
            return ['show_popup' => false, 'reason' => 'ai_decision'];
            
        } catch (\Exception $e) {
            Log::error('AI Popup Decision Failed: ' . $e->getMessage());
            return ['show_popup' => false, 'reason' => 'error'];
        }
    }

    /**
     * Check frequency limits for popup display
     */
    protected function shouldShowPopupBasedOnFrequency(User $user = null)
    {
        // For anonymous users: max 1 popup per session
        if (!$user) {
            $anonymousShown = session('popup_shown_anonymous', false);
            Log::info('Anonymous popup check', ['shown' => $anonymousShown]);
            return !$anonymousShown;
        }
        
        // For logged-in users: check database for recent popups - TEMPORARILY REDUCED FOR TESTING
        $recentPopup = \App\Models\Offer::where('user_id', $user->id)
            ->where('ai_generated', true)
            ->where('created_at', '>=', Carbon::now()->subMinutes(5)) // TEMPORARY: 5 minutes instead of 6 hours
            ->first();
        
        if ($recentPopup) {
            Log::info('Recent popup found for user', ['user_id' => $user->id, 'popup_id' => $recentPopup->id]);
            return false; // Already shown popup in last 5 minutes
        }
        
        // Check session-based frequency - TEMPORARILY REDUCED FOR TESTING
        $sessionKey = 'popup_shown_user_' . $user->id;
        $sessionShown = session($sessionKey, false);
        
        if ($sessionShown) {
            // Allow popup again after 1 minute instead of 30 minutes (TEMPORARY FOR TESTING)
            $lastShown = session($sessionKey . '_time', 0);
            $timeSinceLast = time() - $lastShown;
            $oneMinute = 60; // TEMPORARY: 1 minute instead of 30 minutes
            
            Log::info('Session popup check', [
                'user_id' => $user->id,
                'session_shown' => $sessionShown,
                'last_shown' => $lastShown,
                'time_since_last' => $timeSinceLast,
                'one_minute' => $oneMinute,
                'can_show' => $timeSinceLast >= $oneMinute
            ]);
            
            if ($timeSinceLast < $oneMinute) {
                return false;
            }
        }
        
        Log::info('Frequency check passed for user', ['user_id' => $user->id]);
        return true;
    }

    /**
     * Check if popup was already shown in current session
     */
    protected function wasPopupShownInSession()
    {
        return session('popup_shown_session', false);
    }

    /**
     * Mark popup as shown in session
     */
    protected function markPopupShownInSession()
    {
        session(['popup_shown_session' => true]);
        
        // Also mark for specific user if logged in
        if (auth()->check()) {
            $sessionKey = 'popup_shown_user_' . auth()->id();
            session([$sessionKey => true]);
            session([$sessionKey . '_time' => time()]);
        } else {
            session(['popup_shown_anonymous' => true]);
        }
    }

    /**
     * Collect data for popup decision
     */
    protected function collectPopupDecisionData(User $user = null, $context)
    {
        $data = [
            'context' => $context,
            'timestamp' => Carbon::now()->toISOString(),
            'business_metrics' => $this->getBusinessMetrics(),
            'user_data' => $user ? $this->getUserData($user) : null,
            'session_data' => $this->getSessionData($user),
            'market_conditions' => $this->getMarketConditions(),
        ];

        return $data;
    }

    /**
     * Get AI decision for popup
     */
    protected function getAIPopupDecision($data)
    {
        try {
            $prompt = $this->buildPopupDecisionPrompt($data);
            
            $response = $this->openAIService->generateCompletion($prompt, [
                'temperature' => 0.7,
                'max_tokens' => 1000
            ]);
            
            return $this->parsePopupDecision($response);
        } catch (\Exception $e) {
            Log::warning('OpenAI API failed, using fallback decision logic: ' . $e->getMessage());
            return $this->getFallbackPopupDecision($data);
        }
    }

    /**
     * Build prompt for popup decision
     */
    protected function buildPopupDecisionPrompt($data)
    {
        return "You are an AI marketing expert for a momo restaurant. Based on the following data, decide if a popup offer should be shown.

Data:
" . json_encode($data, JSON_PRETTY_PRINT) . "

Respond in this JSON format:
{
  \"should_show\": true/false,
  \"timing\": \"immediate|delayed|exit_intent\",
  \"urgency\": \"low|medium|high\",
  \"offer_type\": \"discount|bogo|flash|personalized|loyalty\",
  \"target_discount\": 10-50,
  \"reasoning\": \"Detailed explanation of the decision\"
}

GUIDELINES:
1. Show popups to engage users and increase sales
2. Prefer showing to returning customers but also welcome new customers
3. Show during both peak and off-peak hours to maximize opportunities
4. Consider user engagement level - show to users who are browsing
5. Offer relevant discounts based on user behavior
6. Create urgency with limited-time offers
7. Personalize offers when possible

Consider:
1. User behavior and history
2. Current business performance
3. Time of day and context
4. User engagement level
5. Market conditions and competition
6. Inventory levels and sales goals
7. Seasonal trends and holidays

Be strategic but not overly restrictive. The goal is to provide value to customers while driving sales.";
    }

    /**
     * Parse AI popup decision
     */
    protected function parsePopupDecision($response)
    {
        try {
            // Extract JSON from response
            $jsonStart = strpos($response, '{');
            $jsonEnd = strrpos($response, '}') + 1;
            
            if ($jsonStart !== false && $jsonEnd !== false) {
                $jsonString = substr($response, $jsonStart, $jsonEnd - $jsonStart);
                $decision = json_decode($jsonString, true);
                
                if (is_array($decision)) {
                    return array_merge([
                        'should_show' => false,
                        'timing' => 'immediate',
                        'urgency' => 'low',
                        'offer_type' => 'discount',
                        'target_discount' => 15,
                        'reasoning' => 'Default decision'
                    ], $decision);
                }
            }
            
            return [
                'should_show' => false,
                'timing' => 'immediate',
                'urgency' => 'low',
                'offer_type' => 'discount',
                'target_discount' => 15,
                'reasoning' => 'AI parsing failed, defaulting to no popup'
            ];
            
        } catch (\Exception $e) {
            Log::warning('Popup decision parsing failed: ' . $e->getMessage());
            return [
                'should_show' => false,
                'timing' => 'immediate',
                'urgency' => 'low',
                'offer_type' => 'discount',
                'target_discount' => 15,
                'reasoning' => 'Parsing error, no popup'
            ];
        }
    }

    /**
     * Fallback popup decision when OpenAI is not available
     */
    protected function getFallbackPopupDecision($data)
    {
        $user = auth()->user();
        $context = $data['context'] ?? 'homepage';
        
        // Simple fallback logic that shows popups more frequently
        $shouldShow = false;
        $reasoning = '';
        
        // Show popup to new users (no orders)
        if ($user && $user->orders()->count() === 0) {
            $shouldShow = true;
            $reasoning = 'New customer - welcome offer';
        }
        // Show popup to returning customers occasionally
        elseif ($user && $user->orders()->count() > 0) {
            // 40% chance for returning customers
            $shouldShow = (rand(1, 100) <= 40);
            $reasoning = $shouldShow ? 'Returning customer engagement' : 'Returning customer - no popup this time';
        }
        // Show popup to anonymous users occasionally
        else {
            // 30% chance for anonymous users
            $shouldShow = (rand(1, 100) <= 30);
            $reasoning = $shouldShow ? 'Anonymous user engagement' : 'Anonymous user - no popup this time';
        }
        
        // Additional fallback: 20% chance regardless of user type
        if (!$shouldShow && rand(1, 100) <= 20) {
            $shouldShow = true;
            $reasoning = 'Fallback popup to engage user';
        }
        
        return [
            'should_show' => $shouldShow,
            'timing' => 'immediate',
            'urgency' => $shouldShow ? 'medium' : 'low',
            'offer_type' => $shouldShow ? 'discount' : 'none',
            'target_discount' => $shouldShow ? rand(10, 30) : 0,
            'reasoning' => $reasoning
        ];
    }

    /**
     * Get optimal offer for popup
     */
    protected function getOptimalOffer(User $user = null, $decision, $context)
    {
        // Try to find existing suitable offer
        $existingOffer = $this->findSuitableOffer($user, $decision);
        
        if ($existingOffer) {
            return $existingOffer;
        }
        
        // Generate new AI offer if needed
        if ($user) {
            $personalizedOffers = $this->aiOfferService->generatePersonalizedOffers($user, 1);
            if ($personalizedOffers['success'] && !empty($personalizedOffers['offers'])) {
                return $personalizedOffers['offers'][0];
            }
        }
        
        // Generate general AI offer
        $aiOffers = $this->aiOfferService->generateAIOffers(1);
        if ($aiOffers['success'] && !empty($aiOffers['offers'])) {
            return $aiOffers['offers'][0];
        }
        
        // Fallback to default offer
        return $this->createFallbackOffer($decision);
    }

    /**
     * Find suitable existing offer
     */
    protected function findSuitableOffer(User $user = null, $decision)
    {
        $query = Offer::active()
            ->where('type', $decision['offer_type'])
            ->where('discount', '>=', $decision['target_discount'] * 0.8) // Within 20% of target
            ->where('discount', '<=', $decision['target_discount'] * 1.2);
        
        if ($user) {
            // Prefer personalized offers for logged-in users
            $personalizedOffer = $query->clone()
                ->personalized()
                ->forUser($user->id)
                ->first();
            
            if ($personalizedOffer) {
                return $personalizedOffer;
            }
        }
        
        // Fall back to general offers
        return $query->first();
    }

    /**
     * Create fallback offer
     */
    protected function createFallbackOffer($decision)
    {
        $offer = new Offer();
        $offer->title = '🎁 Exclusive Offer Just for You!';
        $offer->description = 'Special limited-time discount crafted based on your preferences. Grab it before it expires!';
        $offer->discount = $decision['target_discount'];
        $offer->code = 'SPECIAL' . Str::random(6);
        $offer->min_purchase = 20.00;
        $offer->max_discount = $decision['target_discount'] * 2;
        $offer->valid_from = Carbon::now();
        $offer->valid_until = Carbon::now()->addDays(3);
        $offer->is_active = true;
        $offer->type = $decision['offer_type'];
        $offer->target_audience = 'all';
        $offer->ai_generated = true;
        $offer->ai_reasoning = $decision['reasoning'];
        $offer->branch_id = 1;
        
        $offer->save();
        
        return $offer;
    }

    /**
     * Get business metrics
     */
    protected function getBusinessMetrics()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        
        return [
            'today_sales' => Order::whereDate('created_at', $today)->sum('total_amount'),
            'week_sales' => Order::where('created_at', '>=', $thisWeek)->sum('total_amount'),
            'today_orders' => Order::whereDate('created_at', $today)->count(),
            'week_orders' => Order::where('created_at', '>=', $thisWeek)->count(),
            'average_order_value' => Order::where('created_at', '>=', $thisWeek)->avg('total_amount'),
            'low_stock_items' => Product::where('stock', '<', 10)->count(),
            'excess_stock_items' => Product::where('stock', '>', 100)->count(),
        ];
    }

    /**
     * Get user data for popup decision
     */
    protected function getUserData(User $user)
    {
        $userOrders = Order::where('user_id', $user->id)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalSpent = $userOrders->sum('total_amount');
        $orderCount = $userOrders->count();
        $lastOrderDate = $userOrders->first()?->created_at;
        $daysSinceLastOrder = $lastOrderDate ? Carbon::now()->diffInDays($lastOrderDate) : null;
        
        return [
            'user_id' => $user->id,
            'total_spent' => $totalSpent,
            'order_count' => $orderCount,
            'average_order_value' => $orderCount > 0 ? $totalSpent / $orderCount : 0,
            'days_since_last_order' => $daysSinceLastOrder,
            'customer_lifetime_value' => $totalSpent,
            'is_vip' => $totalSpent > 1000,
            'is_loyal' => $orderCount > 5,
            'is_new' => $orderCount <= 1,
        ];
    }

    /**
     * Get session data
     */
    protected function getSessionData(User $user = null)
    {
        return [
            'time_of_day' => Carbon::now()->format('H:i'),
            'day_of_week' => Carbon::now()->format('l'),
            'is_weekend' => Carbon::now()->isWeekend(),
            'is_peak_hours' => $this->isPeakHours(),
            'user_logged_in' => $user !== null,
            'session_duration' => session('session_start') ? Carbon::now()->diffInMinutes(session('session_start')) : 0,
        ];
    }

    /**
     * Get market conditions
     */
    protected function getMarketConditions()
    {
        return [
            'is_holiday_season' => $this->isHolidaySeason(),
            'is_payday_week' => $this->isPaydayWeek(),
            'weather_condition' => 'unknown', // Could integrate weather API
            'local_events' => [], // Could integrate events API
            'competition_level' => 'medium', // Could analyze competitor data
        ];
    }

    /**
     * Check if current time is peak hours
     */
    protected function isPeakHours()
    {
        $hour = Carbon::now()->hour;
        return ($hour >= 11 && $hour <= 14) || ($hour >= 17 && $hour <= 21);
    }

    /**
     * Check if it's holiday season
     */
    protected function isHolidaySeason()
    {
        $month = Carbon::now()->month;
        return in_array($month, [11, 12]); // November and December
    }

    /**
     * Check if it's payday week
     */
    protected function isPaydayWeek()
    {
        $day = Carbon::now()->day;
        return $day >= 25 || $day <= 5; // End/beginning of month
    }

    /**
     * Track popup interaction
     */
    public function trackPopupInteraction($offerId, $action, User $user = null)
    {
        try {
            $interaction = [
                'offer_id' => $offerId,
                'action' => $action, // 'shown', 'clicked', 'converted', 'dismissed'
                'user_id' => $user?->id,
                'timestamp' => Carbon::now()->toISOString(),
                'session_id' => session()->getId(),
            ];
            
            // Store interaction for AI learning
            Log::info('Popup Interaction: ' . json_encode($interaction));
            
            // Could store in database for analytics
            // DB::table('popup_interactions')->insert($interaction);
            
        } catch (\Exception $e) {
            Log::error('Failed to track popup interaction: ' . $e->getMessage());
        }
    }

    /**
     * Reset popup frequency limits for testing
     */
    public function resetPopupFrequency(User $user = null)
    {
        if ($user) {
            $sessionKey = 'popup_shown_user_' . $user->id;
            session()->forget($sessionKey);
            session()->forget($sessionKey . '_time');
        } else {
            session()->forget('popup_shown_anonymous');
        }
        session()->forget('popup_shown_session');
        
        Log::info('Popup frequency reset', ['user_id' => $user ? $user->id : 'anonymous']);
        return true;
    }
} 