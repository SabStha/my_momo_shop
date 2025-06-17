<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\ChurnPrediction;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ChurnPredictionService
{
    public function calculateChurnProbability(Customer $customer, Branch $branch): float
    {
        // Get customer's purchase history
        $purchaseHistory = $customer->purchases()
            ->where('branch_id', $branch->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate time-based metrics
        $lastPurchaseDate = $purchaseHistory->first()?->created_at;
        $daysSinceLastPurchase = $lastPurchaseDate ? Carbon::now()->diffInDays($lastPurchaseDate) : 999;
        
        // Calculate frequency metrics
        $purchaseFrequency = $this->calculatePurchaseFrequency($purchaseHistory);
        $averageOrderValue = $this->calculateAverageOrderValue($purchaseHistory);
        
        // Calculate engagement metrics
        $engagementScore = $this->calculateEngagementScore($customer, $branch);
        
        // Calculate risk factors
        $riskFactors = [
            'days_since_last_purchase' => $daysSinceLastPurchase,
            'purchase_frequency' => $purchaseFrequency,
            'average_order_value' => $averageOrderValue,
            'engagement_score' => $engagementScore
        ];
        
        // Calculate churn probability using weighted factors
        $churnProbability = $this->calculateWeightedProbability($riskFactors);
        
        // Store the prediction
        ChurnPrediction::updateOrCreate(
            [
                'customer_id' => $customer->id,
                'branch_id' => $branch->id
            ],
            [
                'churn_probability' => $churnProbability,
                'risk_factors' => $riskFactors,
                'last_updated' => now()
            ]
        );
        
        return $churnProbability;
    }
    
    private function calculatePurchaseFrequency($purchases): float
    {
        if ($purchases->isEmpty()) {
            return 0;
        }
        
        $firstPurchase = $purchases->last()->created_at;
        $lastPurchase = $purchases->first()->created_at;
        $daysBetween = $firstPurchase->diffInDays($lastPurchase);
        
        if ($daysBetween === 0) {
            return $purchases->count();
        }
        
        return $purchases->count() / ($daysBetween / 30); // Purchases per month
    }
    
    private function calculateAverageOrderValue($purchases): float
    {
        if ($purchases->isEmpty()) {
            return 0;
        }
        
        return $purchases->avg('total_amount');
    }
    
    private function calculateEngagementScore(Customer $customer, Branch $branch): float
    {
        $score = 0;
        
        // Check if customer has a loyalty card
        if ($customer->loyaltyCard) {
            $score += 0.3;
        }
        
        // Check if customer has saved payment methods
        if ($customer->paymentMethods()->exists()) {
            $score += 0.2;
        }
        
        // Check if customer has participated in promotions
        $promotionCount = $customer->promotions()
            ->where('branch_id', $branch->id)
            ->count();
        $score += min($promotionCount * 0.1, 0.3);
        
        // Check if customer has provided feedback
        $feedbackCount = $customer->feedback()
            ->where('branch_id', $branch->id)
            ->count();
        $score += min($feedbackCount * 0.1, 0.2);
        
        return $score;
    }
    
    private function calculateWeightedProbability(array $riskFactors): float
    {
        $weights = [
            'days_since_last_purchase' => 0.4,
            'purchase_frequency' => 0.3,
            'average_order_value' => 0.2,
            'engagement_score' => 0.1
        ];
        
        $probability = 0;
        
        // Days since last purchase (higher = more likely to churn)
        $daysScore = min($riskFactors['days_since_last_purchase'] / 90, 1);
        $probability += $daysScore * $weights['days_since_last_purchase'];
        
        // Purchase frequency (lower = more likely to churn)
        $frequencyScore = 1 - min($riskFactors['purchase_frequency'] / 4, 1);
        $probability += $frequencyScore * $weights['purchase_frequency'];
        
        // Average order value (lower = more likely to churn)
        $aovScore = 1 - min($riskFactors['average_order_value'] / 100, 1);
        $probability += $aovScore * $weights['average_order_value'];
        
        // Engagement score (lower = more likely to churn)
        $probability += (1 - $riskFactors['engagement_score']) * $weights['engagement_score'];
        
        return round($probability * 100, 2);
    }
    
    public function getHighRiskCustomers(Branch $branch, float $threshold = 70.0)
    {
        return ChurnPrediction::with('customer')
            ->where('branch_id', $branch->id)
            ->where('churn_probability', '>=', $threshold)
            ->orderBy('churn_probability', 'desc')
            ->get();
    }
} 