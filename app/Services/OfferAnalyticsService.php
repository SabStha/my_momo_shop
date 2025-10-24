<?php

namespace App\Services;

use App\Models\Offer;
use App\Models\OfferClaim;
use App\Models\OfferAnalytics;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Tracks and analyzes offer performance
 */
class OfferAnalyticsService
{
    /**
     * Track offer action
     */
    public function trackAction(
        Offer $offer, 
        User $user, 
        string $action, 
        ?array $metadata = null
    ): void {
        OfferAnalytics::create([
            'offer_id' => $offer->id,
            'user_id' => $user->id,
            'action' => $action,
            'timestamp' => now(),
            'device_info' => $metadata['device_info'] ?? null,
            'session_data' => $metadata['session_data'] ?? null,
            'notification_id' => $metadata['notification_id'] ?? null,
            'discount_value' => $metadata['discount_value'] ?? null,
        ]);
    }

    /**
     * Get offer performance summary
     */
    public function getOfferPerformance(Offer $offer): array
    {
        $analytics = OfferAnalytics::where('offer_id', $offer->id)->get();
        
        $received = $analytics->where('action', 'received')->count();
        $viewed = $analytics->where('action', 'viewed')->count();
        $claimed = $analytics->where('action', 'claimed')->count();
        $applied = $analytics->where('action', 'applied')->count();
        $used = $analytics->where('action', 'used')->count();
        
        // Calculate conversion rates
        $claimRate = $received > 0 ? ($claimed / $received) * 100 : 0;
        $redemptionRate = $claimed > 0 ? ($used / $claimed) * 100 : 0;
        $overallConversion = $received > 0 ? ($used / $received) * 100 : 0;
        
        // Calculate revenue impact
        $totalDiscountGiven = $analytics->where('action', 'used')->sum('discount_value');
        $estimatedRevenue = $used * ($offer->min_purchase ?? 0);
        $roi = $totalDiscountGiven > 0 ? (($estimatedRevenue - $totalDiscountGiven) / $totalDiscountGiven) * 100 : 0;
        
        return [
            'offer_id' => $offer->id,
            'offer_title' => $offer->title,
            'offer_code' => $offer->code,
            'metrics' => [
                'received' => $received,
                'viewed' => $viewed,
                'claimed' => $claimed,
                'applied' => $applied,
                'used' => $used,
            ],
            'conversion_rates' => [
                'claim_rate' => round($claimRate, 2),
                'redemption_rate' => round($redemptionRate, 2),
                'overall_conversion' => round($overallConversion, 2),
            ],
            'financial' => [
                'total_discount_given' => round($totalDiscountGiven, 2),
                'estimated_revenue' => round($estimatedRevenue, 2),
                'roi_percentage' => round($roi, 2),
            ],
            'status' => $this->getOfferStatus($offer, $claimRate, $redemptionRate),
        ];
    }

    /**
     * Get performance comparison for multiple offers
     */
    public function compareOffers(array $offerIds): array
    {
        $comparisons = [];
        
        foreach ($offerIds as $offerId) {
            $offer = Offer::find($offerId);
            if ($offer) {
                $comparisons[] = $this->getOfferPerformance($offer);
            }
        }
        
        return [
            'offers' => $comparisons,
            'best_performing' => $this->findBestPerforming($comparisons),
            'insights' => $this->generateInsights($comparisons),
        ];
    }

    /**
     * Get analytics dashboard data
     */
    public function getDashboardData(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();
        
        $offers = Offer::whereBetween('created_at', [$startDate, $endDate])->get();
        
        $totalOffers = $offers->count();
        $activeOffers = $offers->where('is_active', true)->count();
        $totalClaims = OfferClaim::whereBetween('claimed_at', [$startDate, $endDate])->count();
        $totalRedemptions = OfferClaim::where('status', 'used')
            ->whereBetween('used_at', [$startDate, $endDate])
            ->count();
        
        $analytics = OfferAnalytics::whereBetween('timestamp', [$startDate, $endDate])->get();
        
        $totalSent = $analytics->where('action', 'received')->count();
        $totalClaimed = $analytics->where('action', 'claimed')->count();
        $totalUsed = $analytics->where('action', 'used')->count();
        
        $claimRate = $totalSent > 0 ? ($totalClaimed / $totalSent) * 100 : 0;
        $redemptionRate = $totalClaimed > 0 ? ($totalUsed / $totalClaimed) * 100 : 0;
        
        $totalDiscountGiven = $analytics->where('action', 'used')->sum('discount_value');
        
        return [
            'summary' => [
                'total_offers_created' => $totalOffers,
                'active_offers' => $activeOffers,
                'total_claims' => $totalClaims,
                'total_redemptions' => $totalRedemptions,
                'claim_rate' => round($claimRate, 2),
                'redemption_rate' => round($redemptionRate, 2),
                'total_discount_given' => round($totalDiscountGiven, 2),
            ],
            'top_performing_offers' => $this->getTopPerformingOffers($startDate, $endDate),
            'offer_type_breakdown' => $this->getOfferTypeBreakdown($offers),
            'timeline' => $this->getOfferTimeline($startDate, $endDate),
            'user_segments' => $this->getUserSegmentPerformance($startDate, $endDate),
        ];
    }

    /**
     * Get top performing offers
     */
    protected function getTopPerformingOffers(Carbon $startDate, Carbon $endDate, int $limit = 10): array
    {
        $offers = Offer::whereBetween('created_at', [$startDate, $endDate])->get();
        
        $performance = $offers->map(function($offer) {
            return $this->getOfferPerformance($offer);
        })->sortByDesc('conversion_rates.overall_conversion')->take($limit);
        
        return $performance->values()->toArray();
    }

    /**
     * Get offer type breakdown
     */
    protected function getOfferTypeBreakdown($offers): array
    {
        return $offers->groupBy('type')->map(function($group) {
            return [
                'count' => $group->count(),
                'avg_discount' => round($group->avg('discount'), 2),
                'total_claims' => $group->sum(function($offer) {
                    return $offer->claims()->count();
                }),
            ];
        })->toArray();
    }

    /**
     * Get timeline data
     */
    protected function getOfferTimeline(Carbon $startDate, Carbon $endDate): array
    {
        $days = [];
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            $dayData = OfferAnalytics::whereDate('timestamp', $current->toDateString())
                ->select('action', DB::raw('count(*) as count'))
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray();
            
            $days[] = [
                'date' => $current->toDateString(),
                'received' => $dayData['received'] ?? 0,
                'claimed' => $dayData['claimed'] ?? 0,
                'used' => $dayData['used'] ?? 0,
            ];
            
            $current->addDay();
        }
        
        return $days;
    }

    /**
     * Get user segment performance
     */
    protected function getUserSegmentPerformance(Carbon $startDate, Carbon $endDate): array
    {
        $segments = [
            'new_customers' => User::whereDoesntHave('orders')->pluck('id'),
            'returning_customers' => User::whereHas('orders', function($q) {
                $q->where('created_at', '>=', now()->subDays(30));
            })->pluck('id'),
            'vip_customers' => User::whereHas('orders', function($q) {
                $q->havingRaw('SUM(total_amount) >= 5000');
            })->pluck('id'),
        ];
        
        $performance = [];
        
        foreach ($segments as $segment => $userIds) {
            $analytics = OfferAnalytics::whereIn('user_id', $userIds)
                ->whereBetween('timestamp', [$startDate, $endDate])
                ->get();
            
            $received = $analytics->where('action', 'received')->count();
            $claimed = $analytics->where('action', 'claimed')->count();
            $used = $analytics->where('action', 'used')->count();
            
            $performance[$segment] = [
                'total_users' => $userIds->count(),
                'received' => $received,
                'claimed' => $claimed,
                'used' => $used,
                'claim_rate' => $received > 0 ? round(($claimed / $received) * 100, 2) : 0,
                'redemption_rate' => $claimed > 0 ? round(($used / $claimed) * 100, 2) : 0,
            ];
        }
        
        return $performance;
    }

    /**
     * Find best performing offer
     */
    protected function findBestPerforming(array $comparisons): ?array
    {
        if (empty($comparisons)) return null;
        
        return collect($comparisons)
            ->sortByDesc('conversion_rates.overall_conversion')
            ->first();
    }

    /**
     * Generate insights from data
     */
    protected function generateInsights(array $comparisons): array
    {
        $insights = [];
        
        foreach ($comparisons as $offer) {
            $claimRate = $offer['conversion_rates']['claim_rate'];
            $redemptionRate = $offer['conversion_rates']['redemption_rate'];
            
            if ($claimRate < 10) {
                $insights[] = "{$offer['offer_title']}: Low claim rate ({$claimRate}%) - consider improving title or discount amount";
            }
            
            if ($claimRate > 30) {
                $insights[] = "{$offer['offer_title']}: Excellent claim rate ({$claimRate}%) - this offer resonates well!";
            }
            
            if ($redemptionRate < 30) {
                $insights[] = "{$offer['offer_title']}: Low redemption ({$redemptionRate}%) - users claim but don't use. Consider lowering min_purchase";
            }
            
            if ($offer['financial']['roi_percentage'] < 50) {
                $insights[] = "{$offer['offer_title']}: Low ROI ({$offer['financial']['roi_percentage']}%) - discount may be too high";
            }
        }
        
        return $insights;
    }

    /**
     * Get offer status based on performance
     */
    protected function getOfferStatus(Offer $offer, float $claimRate, float $redemptionRate): string
    {
        if ($claimRate >= 25 && $redemptionRate >= 40) {
            return 'excellent';
        } elseif ($claimRate >= 15 && $redemptionRate >= 25) {
            return 'good';
        } elseif ($claimRate >= 5 || $redemptionRate >= 15) {
            return 'average';
        } else {
            return 'poor';
        }
    }

    /**
     * Get real-time offer stats
     */
    public function getRealTimeStats(): array
    {
        $today = Carbon::today();
        
        $todayAnalytics = OfferAnalytics::whereDate('timestamp', $today)->get();
        
        return [
            'today' => [
                'offers_sent' => $todayAnalytics->where('action', 'received')->count(),
                'claims' => $todayAnalytics->where('action', 'claimed')->count(),
                'redemptions' => $todayAnalytics->where('action', 'used')->count(),
            ],
            'last_hour' => [
                'offers_sent' => OfferAnalytics::where('action', 'received')
                    ->where('timestamp', '>=', now()->subHour())
                    ->count(),
                'claims' => OfferAnalytics::where('action', 'claimed')
                    ->where('timestamp', '>=', now()->subHour())
                    ->count(),
            ],
            'active_offers_count' => Offer::active()->count(),
            'total_active_claims' => OfferClaim::where('status', 'active')->count(),
        ];
    }
}

