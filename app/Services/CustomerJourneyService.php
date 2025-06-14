<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\CustomerSegment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerJourneyService
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Analyze customer journey and get AI insights
     */
    public function analyzeJourney(string $startDate, string $endDate, int $branchId)
    {
        $journeyData = $this->getJourneyData($startDate, $endDate, $branchId);
        return $this->getAIInsights($journeyData);
    }

    /**
     * Get journey data for analysis
     */
    protected function getJourneyData(string $startDate, string $endDate, int $branchId)
    {
        return [
            'funnel_stages' => $this->getFunnelStages($startDate, $endDate, $branchId),
            'drop_off_points' => $this->getDropOffPoints($startDate, $endDate, $branchId),
            'conversion_rates' => $this->getConversionRates($startDate, $endDate, $branchId),
            'time_to_convert' => $this->getTimeToConvert($startDate, $endDate, $branchId),
            'segment_performance' => $this->getSegmentPerformance($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get AI insights from journey data
     */
    protected function getAIInsights(array $journeyData)
    {
        $prompt = $this->buildAnalysisPrompt($journeyData);
        return $this->openAIService->generateText($prompt);
    }

    /**
     * Build prompt for AI analysis
     */
    protected function buildAnalysisPrompt(array $journeyData)
    {
        return "Analyze this customer journey data and provide strategic insights:\n" .
               "Funnel Stages: " . json_encode($journeyData['funnel_stages']) . "\n" .
               "Drop-off Points: " . json_encode($journeyData['drop_off_points']) . "\n" .
               "Conversion Rates: " . json_encode($journeyData['conversion_rates']) . "\n" .
               "Time to Convert: " . json_encode($journeyData['time_to_convert']) . "\n" .
               "Segment Performance: " . json_encode($journeyData['segment_performance']) . "\n" .
               "Please provide:\n" .
               "1. Key drop-off points and potential causes\n" .
               "2. Recommendations for improving conversion rates\n" .
               "3. Segment-specific insights and opportunities\n" .
               "4. Strategic recommendations for journey optimization";
    }

    /**
     * Get funnel stages data
     */
    protected function getFunnelStages(string $startDate, string $endDate, int $branchId)
    {
        return [
            'new_visitors' => $this->getNewVisitors($startDate, $endDate, $branchId),
            'returning_visitors' => $this->getReturningVisitors($startDate, $endDate, $branchId),
            'first_time_buyers' => $this->getFirstTimeBuyers($startDate, $endDate, $branchId),
            'repeat_buyers' => $this->getRepeatBuyers($startDate, $endDate, $branchId),
            'loyal_customers' => $this->getLoyalCustomers($startDate, $endDate, $branchId)
        ];
    }

    /**
     * Get drop-off points
     */
    protected function getDropOffPoints(string $startDate, string $endDate, int $branchId)
    {
        $stages = $this->getFunnelStages($startDate, $endDate, $branchId);
        $dropOffs = [];

        $previous = null;
        foreach ($stages as $stage => $count) {
            if ($previous !== null) {
                $dropOffs[$stage] = [
                    'count' => $previous - $count,
                    'percentage' => $previous > 0 ? (($previous - $count) / $previous) * 100 : 0
                ];
            }
            $previous = $count;
        }

        return $dropOffs;
    }

    /**
     * Get conversion rates between stages
     */
    protected function getConversionRates(string $startDate, string $endDate, int $branchId)
    {
        $stages = $this->getFunnelStages($startDate, $endDate, $branchId);
        $rates = [];

        $previous = null;
        foreach ($stages as $stage => $count) {
            if ($previous !== null) {
                $rates[$stage] = $previous > 0 ? ($count / $previous) * 100 : 0;
            }
            $previous = $count;
        }

        return $rates;
    }

    /**
     * Get average time to convert between stages
     */
    protected function getTimeToConvert(string $startDate, string $endDate, int $branchId)
    {
        return DB::table('orders')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select(
                'user_id',
                DB::raw('DATEDIFF(MAX(created_at), MIN(created_at)) as days_to_convert')
            )
            ->groupBy('user_id')
            ->having('days_to_convert', '>', 0)
            ->avg('days_to_convert') ?? 0;
    }

    /**
     * Get segment performance metrics
     */
    protected function getSegmentPerformance(string $startDate, string $endDate, int $branchId)
    {
        $segments = CustomerSegment::all();
        $performance = [];

        foreach ($segments as $segment) {
            $users = $segment->users;
            $performance[$segment->name] = [
                'size' => $users->count(),
                'conversion_rate' => $this->getSegmentConversionRate($users, $startDate, $endDate, $branchId),
                'average_order_value' => $this->getSegmentAverageOrderValue($users, $startDate, $endDate, $branchId),
                'retention_rate' => $this->getSegmentRetentionRate($users, $startDate, $endDate, $branchId)
            ];
        }

        return $performance;
    }

    /**
     * Get new visitors count
     */
    protected function getNewVisitors(string $startDate, string $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at');
        })
        ->whereDoesntHave('orders', function ($query) use ($startDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->where('created_at', '<', $startDate)
                ->whereNull('deleted_at');
        })
        ->count();
    }

    /**
     * Get returning visitors count
     */
    protected function getReturningVisitors(string $startDate, string $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at');
        })
        ->whereHas('orders', function ($query) use ($startDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->where('created_at', '<', $startDate)
                ->whereNull('deleted_at');
        })
        ->count();
    }

    /**
     * Get first-time buyers count
     */
    protected function getFirstTimeBuyers(string $startDate, string $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) = 1');
        })
        ->count();
    }

    /**
     * Get repeat buyers count
     */
    protected function getRepeatBuyers(string $startDate, string $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) BETWEEN 2 AND 4');
        })
        ->count();
    }

    /**
     * Get loyal customers count
     */
    protected function getLoyalCustomers(string $startDate, string $endDate, int $branchId)
    {
        return User::whereHas('orders', function ($query) use ($startDate, $endDate, $branchId) {
            $query->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->select('user_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) >= 5');
        })
        ->count();
    }

    /**
     * Get segment conversion rate
     */
    protected function getSegmentConversionRate($users, string $startDate, string $endDate, int $branchId)
    {
        $totalUsers = $users->count();
        if ($totalUsers === 0) return 0;

        $convertedUsers = $users->filter(function ($user) use ($startDate, $endDate, $branchId) {
            return $user->orders()
                ->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->exists();
        })->count();

        return ($convertedUsers / $totalUsers) * 100;
    }

    /**
     * Get segment average order value
     */
    protected function getSegmentAverageOrderValue($users, string $startDate, string $endDate, int $branchId)
    {
        return $users->map(function ($user) use ($startDate, $endDate, $branchId) {
            return $user->orders()
                ->where('branch_id', $branchId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->whereNull('deleted_at')
                ->avg('total') ?? 0;
        })->avg();
    }

    /**
     * Get segment retention rate
     */
    protected function getSegmentRetentionRate($users, string $startDate, string $endDate, int $branchId)
    {
        $totalUsers = $users->count();
        if ($totalUsers === 0) return 0;

        $retainedUsers = $users->filter(function ($user) use ($startDate, $endDate, $branchId) {
            $lastOrder = $user->orders()
                ->where('branch_id', $branchId)
                ->whereNull('deleted_at')
                ->latest()
                ->first();

            return $lastOrder && $lastOrder->created_at->between($startDate, $endDate);
        })->count();

        return ($retainedUsers / $totalUsers) * 100;
    }
} 