<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\User;
use App\Models\CustomerSegment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CampaignService
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Create a new campaign
     */
    public function createCampaign(array $data)
    {
        $campaign = new Campaign();
        $campaign->name = $data['name'];
        $campaign->segment_id = $data['segment_id'];
        $campaign->offer_type = $data['offer_type'];
        $campaign->offer_value = $data['offer_value'];
        $campaign->start_date = $data['start_date'];
        $campaign->end_date = $data['end_date'];
        $campaign->status = 'draft';
        
        // Generate campaign copy using AI
        $campaign->copy = $this->generateCampaignCopy($data);
        
        $campaign->save();
        
        return $campaign;
    }

    /**
     * Generate campaign copy using AI
     */
    protected function generateCampaignCopy(array $data)
    {
        $segment = CustomerSegment::find($data['segment_id']);
        $prompt = "Generate a marketing campaign copy for {$segment->name} segment. " .
                 "Offer type: {$data['offer_type']}, Value: {$data['offer_value']}. " .
                 "Make it engaging and personalized.";

        return $this->openAIService->generateText($prompt);
    }

    /**
     * Get campaign suggestions for a segment
     */
    public function getCampaignSuggestions(int $segmentId)
    {
        $segment = CustomerSegment::findOrFail($segmentId);
        $segmentMetrics = $this->getSegmentMetrics($segmentId);

        $suggestions = [];
        
        // Suggest retention campaign for at-risk customers
        if ($segmentMetrics['churn_risk'] > 0.3) {
            $suggestions[] = [
                'type' => 'retention',
                'offer_type' => 'discount',
                'offer_value' => '20%',
                'reason' => 'High churn risk detected',
                'expected_impact' => 'Increase retention by 15%'
            ];
        }

        // Suggest upsell campaign for loyal customers
        if ($segmentMetrics['loyalty_score'] > 0.7) {
            $suggestions[] = [
                'type' => 'upsell',
                'offer_type' => 'premium_product',
                'offer_value' => 'VIP access',
                'reason' => 'High loyalty score',
                'expected_impact' => 'Increase AOV by 25%'
            ];
        }

        // Suggest reactivation campaign for inactive customers
        if ($segmentMetrics['days_since_last_order'] > 60) {
            $suggestions[] = [
                'type' => 'reactivation',
                'offer_type' => 'special_offer',
                'offer_value' => '30% off',
                'reason' => 'Inactive for more than 60 days',
                'expected_impact' => 'Reactivate 20% of inactive customers'
            ];
        }

        return $suggestions;
    }

    /**
     * Get metrics for a segment
     */
    protected function getSegmentMetrics(int $segmentId)
    {
        $segment = CustomerSegment::findOrFail($segmentId);
        
        return [
            'churn_risk' => $this->calculateSegmentChurnRisk($segment),
            'loyalty_score' => $this->calculateSegmentLoyaltyScore($segment),
            'days_since_last_order' => $this->getAverageDaysSinceLastOrder($segment),
            'average_order_value' => $this->getAverageOrderValue($segment),
            'purchase_frequency' => $this->getPurchaseFrequency($segment)
        ];
    }

    /**
     * Calculate churn risk for a segment
     */
    protected function calculateSegmentChurnRisk(CustomerSegment $segment)
    {
        $users = $segment->users;
        $churnCount = 0;

        foreach ($users as $user) {
            $lastOrder = $user->orders()->latest()->first();
            if ($lastOrder && now()->diffInDays($lastOrder->created_at) > 90) {
                $churnCount++;
            }
        }

        return $users->count() > 0 ? $churnCount / $users->count() : 0;
    }

    /**
     * Calculate loyalty score for a segment
     */
    protected function calculateSegmentLoyaltyScore(CustomerSegment $segment)
    {
        $users = $segment->users;
        if ($users->isEmpty()) return 0;

        $totalScore = 0;
        foreach ($users as $user) {
            $orderCount = $user->orders()->count();
            $avgOrderValue = $user->orders()->avg('total');
            $recency = $this->calculateRecencyScore($user);

            $totalScore += ($orderCount * 0.4 + $avgOrderValue * 0.3 + $recency * 0.3);
        }

        return $totalScore / $users->count();
    }

    /**
     * Calculate recency score for a user
     */
    protected function calculateRecencyScore(User $user)
    {
        $lastOrder = $user->orders()->latest()->first();
        if (!$lastOrder) return 0;

        $daysSinceLastOrder = now()->diffInDays($lastOrder->created_at);
        return max(0, 1 - ($daysSinceLastOrder / 90));
    }

    /**
     * Get average days since last order for a segment
     */
    protected function getAverageDaysSinceLastOrder(CustomerSegment $segment)
    {
        $users = $segment->users;
        if ($users->isEmpty()) return 0;

        $totalDays = 0;
        foreach ($users as $user) {
            $lastOrder = $user->orders()->latest()->first();
            if ($lastOrder) {
                $totalDays += now()->diffInDays($lastOrder->created_at);
            }
        }

        return $totalDays / $users->count();
    }

    /**
     * Get average order value for a segment
     */
    protected function getAverageOrderValue(CustomerSegment $segment)
    {
        return $segment->users()
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->avg('orders.total') ?? 0;
    }

    /**
     * Get purchase frequency for a segment
     */
    protected function getPurchaseFrequency(CustomerSegment $segment)
    {
        $users = $segment->users;
        if ($users->isEmpty()) return 0;

        $totalOrders = 0;
        foreach ($users as $user) {
            $totalOrders += $user->orders()->count();
        }

        return $totalOrders / $users->count();
    }
} 