<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\User;
use App\Models\OfferAnalytics;
use Illuminate\Support\Facades\DB;

/**
 * A/B Testing for offer variants
 */
class ABTestingService
{
    protected $analyticsService;

    public function __construct(OfferAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Create A/B test variants
     */
    public function createABTest(array $variantA, array $variantB, array $config = []): array
    {
        $testId = 'AB_' . \Str::random(8);
        
        // Create variant A
        $offerA = $this->createVariant($variantA, $testId, 'A', $config);
        
        // Create variant B
        $offerB = $this->createVariant($variantB, $testId, 'B', $config);
        
        return [
            'test_id' => $testId,
            'variant_a' => $offerA,
            'variant_b' => $offerB,
            'config' => array_merge([
                'split_ratio' => 0.5, // 50/50 split
                'min_sample_size' => 100,
                'confidence_level' => 0.95,
            ], $config),
        ];
    }

    /**
     * Assign user to variant (A or B)
     */
    public function assignVariant(User $user, string $testId, float $splitRatio = 0.5): string
    {
        // Deterministic assignment based on user ID (consistent per user)
        $hash = crc32($user->id . $testId);
        $random = ($hash % 100) / 100;
        
        return $random < $splitRatio ? 'A' : 'B';
    }

    /**
     * Get A/B test results
     */
    public function getTestResults(string $testId): array
    {
        $offersA = Offer::where('code', 'LIKE', "{$testId}_A%")->get();
        $offersB = Offer::where('code', 'LIKE', "{$testId}_B%")->get();
        
        if ($offersA->isEmpty() || $offersB->isEmpty()) {
            return ['error' => 'Test not found or incomplete'];
        }
        
        $resultsA = $this->getVariantResults($offersA);
        $resultsB = $this->getVariantResults($offersB);
        
        $winner = $this->determineWinner($resultsA, $resultsB);
        $confidence = $this->calculateConfidence($resultsA, $resultsB);
        
        return [
            'test_id' => $testId,
            'variant_a' => $resultsA,
            'variant_b' => $resultsB,
            'winner' => $winner,
            'confidence' => $confidence,
            'statistical_significance' => $confidence >= 0.95,
            'recommendation' => $this->generateRecommendation($resultsA, $resultsB, $winner, $confidence),
        ];
    }

    /**
     * Create variant offer
     */
    protected function createVariant(array $data, string $testId, string $variant, array $config): Offer
    {
        $offer = new Offer();
        $offer->title = $data['title'];
        $offer->description = $data['description'];
        $offer->discount = $data['discount'];
        $offer->code = "{$testId}_{$variant}_" . \Str::random(4);
        $offer->min_purchase = $data['min_purchase'] ?? 20;
        $offer->max_discount = $data['max_discount'] ?? null;
        $offer->valid_from = now();
        $offer->valid_until = now()->addDays($config['test_duration_days'] ?? 7);
        $offer->is_active = true;
        $offer->type = $data['type'] ?? 'ab_test';
        $offer->target_audience = $data['target_audience'] ?? 'all';
        $offer->ai_generated = true;
        $offer->ai_reasoning = "A/B Test {$testId} - Variant {$variant}";
        $offer->branch_id = 1;
        
        $offer->save();
        
        return $offer;
    }

    /**
     * Get variant results
     */
    protected function getVariantResults($offers): array
    {
        $totalReceived = 0;
        $totalClaimed = 0;
        $totalUsed = 0;
        $totalRevenue = 0;
        $totalDiscount = 0;
        
        foreach ($offers as $offer) {
            $perf = $this->analyticsService->getOfferPerformance($offer);
            $totalReceived += $perf['metrics']['received'];
            $totalClaimed += $perf['metrics']['claimed'];
            $totalUsed += $perf['metrics']['used'];
            $totalRevenue += $perf['financial']['estimated_revenue'];
            $totalDiscount += $perf['financial']['total_discount_given'];
        }
        
        return [
            'received' => $totalReceived,
            'claimed' => $totalClaimed,
            'used' => $totalUsed,
            'claim_rate' => $totalReceived > 0 ? round(($totalClaimed / $totalReceived) * 100, 2) : 0,
            'redemption_rate' => $totalClaimed > 0 ? round(($totalUsed / $totalClaimed) * 100, 2) : 0,
            'conversion_rate' => $totalReceived > 0 ? round(($totalUsed / $totalReceived) * 100, 2) : 0,
            'revenue' => round($totalRevenue, 2),
            'discount_given' => round($totalDiscount, 2),
            'roi' => $totalDiscount > 0 ? round((($totalRevenue - $totalDiscount) / $totalDiscount) * 100, 2) : 0,
        ];
    }

    /**
     * Determine winner
     */
    protected function determineWinner(array $resultsA, array $resultsB): string
    {
        $scoreA = $resultsA['conversion_rate'] * $resultsA['roi'];
        $scoreB = $resultsB['conversion_rate'] * $resultsB['roi'];
        
        if ($scoreA > $scoreB * 1.1) {
            return 'A';
        } elseif ($scoreB > $scoreA * 1.1) {
            return 'B';
        } else {
            return 'tie';
        }
    }

    /**
     * Calculate statistical confidence
     */
    protected function calculateConfidence(array $resultsA, array $resultsB): float
    {
        $n1 = $resultsA['received'];
        $n2 = $resultsB['received'];
        $p1 = $resultsA['conversion_rate'] / 100;
        $p2 = $resultsB['conversion_rate'] / 100;
        
        if ($n1 < 30 || $n2 < 30) {
            return 0; // Insufficient sample size
        }
        
        $pPooled = (($n1 * $p1) + ($n2 * $p2)) / ($n1 + $n2);
        $se = sqrt($pPooled * (1 - $pPooled) * ((1 / $n1) + (1 / $n2)));
        
        if ($se == 0) return 0;
        
        $zScore = abs(($p1 - $p2) / $se);
        
        // Approximate confidence from z-score
        if ($zScore >= 1.96) return 0.95;
        if ($zScore >= 1.65) return 0.90;
        if ($zScore >= 1.28) return 0.80;
        
        return 0.50;
    }

    /**
     * Generate recommendation
     */
    protected function generateRecommendation(array $resultsA, array $resultsB, string $winner, float $confidence): string
    {
        if ($resultsA['received'] < 50 || $resultsB['received'] < 50) {
            return "Continue test - insufficient data. Need at least 50 users per variant.";
        }
        
        if ($confidence < 0.90) {
            return "Continue test - not statistically significant yet. Current confidence: " . round($confidence * 100) . "%";
        }
        
        if ($winner === 'tie') {
            return "Results are too close. Both variants perform similarly. Choose based on other factors (cost, brand fit, etc.)";
        }
        
        $winnerResults = $winner === 'A' ? $resultsA : $resultsB;
        
        return "Winner: Variant {$winner} with {$winnerResults['conversion_rate']}% conversion rate and {$winnerResults['roi']}% ROI. Confidence: " . round($confidence * 100) . "%. Recommend rolling out this variant to all users.";
    }

    /**
     * Quick A/B test: Different discount amounts
     */
    public function testDiscountAmounts(string $title, float $discountA, float $discountB, array $baseConfig = []): array
    {
        $variantA = array_merge($baseConfig, [
            'title' => $title,
            'discount' => $discountA,
        ]);
        
        $variantB = array_merge($baseConfig, [
            'title' => $title,
            'discount' => $discountB,
        ]);
        
        return $this->createABTest($variantA, $variantB, [
            'test_type' => 'discount_amount',
            'test_name' => "{$discountA}% vs {$discountB}%",
        ]);
    }

    /**
     * Quick A/B test: Different titles
     */
    public function testOfferTitles(string $titleA, string $titleB, array $baseConfig = []): array
    {
        $variantA = array_merge($baseConfig, [
            'title' => $titleA,
        ]);
        
        $variantB = array_merge($baseConfig, [
            'title' => $titleB,
        ]);
        
        return $this->createABTest($variantA, $variantB, [
            'test_type' => 'title',
            'test_name' => "'{$titleA}' vs '{$titleB}'",
        ]);
    }
}

