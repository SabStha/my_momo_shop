<?php

namespace App\Services;

use App\Models\User;
use App\Models\Offer;
use App\Models\AutomatedOfferTrigger;
use App\Services\UserBehaviorAnalyzer;
use App\Services\AIOfferService;
use App\Services\MobileNotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service for managing automated offer triggers
 */
class AutomatedOfferTriggerService
{
    protected $behaviorAnalyzer;
    protected $aiOfferService;
    protected $notificationService;

    public function __construct(
        UserBehaviorAnalyzer $behaviorAnalyzer,
        AIOfferService $aiOfferService,
        MobileNotificationService $notificationService
    ) {
        $this->behaviorAnalyzer = $behaviorAnalyzer;
        $this->aiOfferService = $aiOfferService;
        $this->notificationService = $notificationService;
    }

    /**
     * Process all active triggers for all users
     */
    public function processAllTriggers()
    {
        $triggers = AutomatedOfferTrigger::active()->orderBy('priority', 'desc')->get();
        $results = [];

        foreach ($triggers as $trigger) {
            Log::info("Processing trigger: {$trigger->name}");
            $result = $this->processTrigger($trigger);
            $results[$trigger->name] = $result;
        }

        return $results;
    }

    /**
     * Process a specific trigger
     */
    public function processTrigger(AutomatedOfferTrigger $trigger)
    {
        $eligibleUsers = $this->findEligibleUsers($trigger);
        $offersCreated = 0;

        foreach ($eligibleUsers as $user) {
            if ($this->shouldSendToUser($user, $trigger)) {
                $offer = $this->generateOfferFromTrigger($user, $trigger);
                
                if ($offer) {
                    $this->notificationService->sendOfferNotification($user, $offer);
                    $offersCreated++;
                }
            }
        }

        return [
            'trigger' => $trigger->name,
            'eligible_users' => count($eligibleUsers),
            'offers_created' => $offersCreated,
        ];
    }

    /**
     * Find users eligible for a trigger
     */
    protected function findEligibleUsers(AutomatedOfferTrigger $trigger)
    {
        $query = User::query();
        $conditions = $trigger->conditions;

        switch ($trigger->trigger_type) {
            case 'new_user_welcome':
                // Users with exactly 1 order in the last 24-72 hours
                $query->whereHas('orders', function($q) {
                    $q->whereBetween('created_at', [now()->subHours(72), now()->subHours(24)]);
                }, '=', 1);
                break;

            case 'inactive_user':
                // Users who haven't ordered in X days
                $daysInactive = $conditions['days_inactive'] ?? 14;
                $query->whereHas('orders', function($q) use ($daysInactive) {
                    $q->where('created_at', '<', now()->subDays($daysInactive))
                      ->where('created_at', '>', now()->subDays($daysInactive + 30));
                });
                break;

            case 'high_value_vip':
                // Users with high lifetime value
                $minValue = $conditions['min_lifetime_value'] ?? 5000;
                $query->whereHas('orders', function($q) use ($minValue) {
                    $q->select(\DB::raw('SUM(total_amount) as total'))
                      ->having('total', '>=', $minValue);
                });
                break;

            case 'birthday':
                // Users whose birthday is today
                $query->whereNotNull('birth_date')
                      ->whereRaw('DAY(birth_date) = DAY(NOW())')
                      ->whereRaw('MONTH(birth_date) = MONTH(NOW())');
                break;

            case 'milestone':
                // Users who just hit a milestone (10th, 25th, 50th order)
                $milestones = $conditions['milestones'] ?? [10, 25, 50, 100];
                // This requires more complex logic - implement based on needs
                break;
        }

        return $query->limit(100)->get(); // Limit to prevent overload
    }

    /**
     * Check if user should receive offer from this trigger
     */
    protected function shouldSendToUser(User $user, AutomatedOfferTrigger $trigger): bool
    {
        // Check behavior analyzer
        if (!$this->behaviorAnalyzer->shouldReceiveOffer($user, $trigger->trigger_type)) {
            return false;
        }

        // Check cooldown - has user received this trigger recently?
        $lastSent = \DB::table('offer_analytics')
            ->where('user_id', $user->id)
            ->whereHas('offer', function($q) use ($trigger) {
                $q->where('type', $trigger->trigger_type);
            })
            ->where('action', 'received')
            ->where('timestamp', '>=', now()->subDays($trigger->cooldown_days))
            ->exists();

        if ($lastSent) {
            Log::info("User {$user->id} in cooldown for trigger {$trigger->name}");
            return false;
        }

        // Check max uses
        if ($trigger->max_uses_per_user) {
            $usageCount = \DB::table('offer_analytics')
                ->where('user_id', $user->id)
                ->whereHas('offer', function($q) use ($trigger) {
                    $q->where('type', $trigger->trigger_type);
                })
                ->where('action', 'received')
                ->count();

            if ($usageCount >= $trigger->max_uses_per_user) {
                Log::info("User {$user->id} reached max uses for trigger {$trigger->name}");
                return false;
            }
        }

        return true;
    }

    /**
     * Generate personalized offer from trigger template
     */
    protected function generateOfferFromTrigger(User $user, AutomatedOfferTrigger $trigger): ?Offer
    {
        try {
            $template = $trigger->offer_template;
            $behaviorProfile = $this->behaviorAnalyzer->getUserBehaviorProfile($user);

            // Personalize the template based on user data
            $personalizedData = $this->personalizeOfferTemplate($template, $user, $behaviorProfile);

            // Create the offer
            $offer = new Offer();
            $offer->title = $personalizedData['title'];
            $offer->description = $personalizedData['description'];
            $offer->discount = $personalizedData['discount'];
            $offer->code = $this->generateUniqueCode($trigger->trigger_type);
            $offer->min_purchase = $personalizedData['min_purchase'];
            $offer->max_discount = $personalizedData['max_discount'];
            $offer->valid_from = Carbon::now();
            $offer->valid_until = Carbon::now()->addDays($personalizedData['valid_days'] ?? 7);
            $offer->is_active = true;
            $offer->type = $trigger->trigger_type;
            $offer->target_audience = 'personalized';
            $offer->ai_generated = true;
            $offer->ai_reasoning = "Automated trigger: {$trigger->name}. " . json_encode($behaviorProfile['recommendations']);
            $offer->branch_id = 1; // Default branch
            $offer->user_id = $user->id;

            $offer->save();

            Log::info("Created automated offer for user {$user->id} from trigger {$trigger->name}", [
                'offer_id' => $offer->id,
                'code' => $offer->code
            ]);

            return $offer;

        } catch (\Exception $e) {
            Log::error("Failed to generate offer from trigger {$trigger->name}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Personalize offer template based on user data
     */
    protected function personalizeOfferTemplate(array $template, User $user, array $behaviorProfile): array
    {
        $valueMetrics = $behaviorProfile['value_metrics'];
        $churnRisk = $behaviorProfile['churn_risk'];

        // Adjust discount based on user value and churn risk
        $baseDiscount = $template['discount'] ?? 10;
        
        if ($churnRisk === 'high') {
            $baseDiscount *= 1.5; // 50% more discount for high churn risk
        } elseif ($valueMetrics['high_value_customer']) {
            $baseDiscount *= 1.2; // 20% more for VIPs
        }

        // Personalize min purchase based on average order value
        $minPurchase = max(
            $template['min_purchase'] ?? 20,
            $valueMetrics['average_order_value'] * 0.7
        );

        // Replace placeholders in text
        $title = str_replace(
            ['{name}', '{tier}'],
            [$user->name, $valueMetrics['value_tier']],
            $template['title'] ?? 'Special Offer for You'
        );

        $description = str_replace(
            ['{name}', '{tier}'],
            [$user->name, $valueMetrics['value_tier']],
            $template['description'] ?? 'Exclusive offer just for you!'
        );

        return [
            'title' => $title,
            'description' => $description,
            'discount' => min($baseDiscount, 30), // Cap at 30%
            'min_purchase' => $minPurchase,
            'max_discount' => $baseDiscount * 2,
            'valid_days' => $template['valid_days'] ?? 7,
        ];
    }

    /**
     * Generate unique offer code based on trigger type
     */
    protected function generateUniqueCode(string $triggerType): string
    {
        $prefix = match($triggerType) {
            'new_user_welcome' => 'WELCOME',
            'inactive_user' => 'COMEBACK',
            'birthday' => 'BDAY',
            'high_value_vip' => 'VIP',
            'milestone' => 'MILESTONE',
            default => 'SPECIAL',
        };

        do {
            $code = $prefix . Str::random(6);
            $exists = Offer::where('code', $code)->exists();
        } while ($exists);

        return $code;
    }

    /**
     * Create default triggers (seed data)
     */
    public function createDefaultTriggers()
    {
        $defaultTriggers = [
            [
                'name' => 'Welcome Offer - First Order',
                'trigger_type' => 'new_user_welcome',
                'description' => 'Send welcome offer to new users after their first order',
                'conditions' => ['orders_count' => 1, 'hours_after_first_order' => 24],
                'offer_template' => [
                    'title' => 'Welcome Back, {name}! 🎉',
                    'description' => 'Thank you for your first order! Here is a special discount for your next visit.',
                    'discount' => 15,
                    'min_purchase' => 20,
                    'valid_days' => 7,
                ],
                'priority' => 10,
                'max_uses_per_user' => 1,
                'cooldown_days' => 999, // Once per user forever
            ],
            [
                'name' => 'Inactive User Win-Back (14 Days)',
                'trigger_type' => 'inactive_user',
                'description' => 'Re-engage users who haven\'t ordered in 14 days',
                'conditions' => ['days_inactive' => 14],
                'offer_template' => [
                    'title' => 'We Miss You! 💝',
                    'description' => 'It\'s been a while since your last order. Come back and enjoy this special discount!',
                    'discount' => 20,
                    'min_purchase' => 25,
                    'valid_days' => 5,
                ],
                'priority' => 8,
                'max_uses_per_user' => null,
                'cooldown_days' => 30,
            ],
            [
                'name' => 'Birthday Special',
                'trigger_type' => 'birthday',
                'description' => 'Send birthday offer to users on their birthday',
                'conditions' => ['birthday_match' => true],
                'offer_template' => [
                    'title' => 'Happy Birthday, {name}! 🎂',
                    'description' => 'Celebrate your special day with us! Enjoy this birthday discount.',
                    'discount' => 25,
                    'min_purchase' => 15,
                    'valid_days' => 3,
                ],
                'priority' => 9,
                'max_uses_per_user' => 1,
                'cooldown_days' => 365, // Once per year
            ],
            [
                'name' => 'VIP Exclusive - High Value Customers',
                'trigger_type' => 'high_value_vip',
                'description' => 'Monthly exclusive offer for high-value customers',
                'conditions' => ['min_lifetime_value' => 5000],
                'offer_template' => [
                    'title' => 'VIP Exclusive for {tier} Members! ⭐',
                    'description' => 'Thank you for being a valued customer! Enjoy this exclusive VIP reward.',
                    'discount' => 20,
                    'min_purchase' => 30,
                    'valid_days' => 10,
                ],
                'priority' => 7,
                'max_uses_per_user' => null,
                'cooldown_days' => 30,
            ],
        ];

        foreach ($defaultTriggers as $triggerData) {
            AutomatedOfferTrigger::updateOrCreate(
                ['trigger_type' => $triggerData['trigger_type']],
                $triggerData
            );
        }

        Log::info('Default automated triggers created/updated');
    }

    /**
     * Process specific trigger type for all eligible users
     */
    public function processTriggerType(string $triggerType)
    {
        $trigger = AutomatedOfferTrigger::active()
            ->where('trigger_type', $triggerType)
            ->first();

        if (!$trigger) {
            Log::warning("No active trigger found for type: {$triggerType}");
            return ['success' => false, 'error' => 'Trigger not found'];
        }

        return $this->processTrigger($trigger);
    }

    /**
     * Check and send offer for specific user and trigger type
     */
    public function checkAndSendOffer(User $user, string $triggerType)
    {
        $trigger = AutomatedOfferTrigger::active()
            ->where('trigger_type', $triggerType)
            ->first();

        if (!$trigger) {
            return false;
        }

        if (!$this->shouldSendToUser($user, $trigger)) {
            return false;
        }

        $offer = $this->generateOfferFromTrigger($user, $trigger);
        
        if ($offer) {
            $this->notificationService->sendOfferNotification($user, $offer);
            return true;
        }

        return false;
    }
}

