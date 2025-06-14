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
     * Get detailed drop-off analysis with AI insights
     */
    public function getDetailedDropOffAnalysis(string $startDate, string $endDate, int $branchId)
    {
        $journeyData = $this->getJourneyData($startDate, $endDate, $branchId);
        $dropOffPoints = $this->getDropOffPoints($startDate, $endDate, $branchId);
        
        // Get detailed metrics for each stage
        $stageMetrics = [];
        foreach ($journeyData['funnel_stages'] as $stage => $count) {
            $stageMetrics[$stage] = [
                'count' => $count,
                'conversion_rate' => $this->getStageConversionRate($stage, $journeyData),
                'average_time' => $this->getAverageTimeInStage($stage, $startDate, $endDate, $branchId),
                'retention_rate' => $this->getStageRetentionRate($stage, $startDate, $endDate, $branchId)
            ];
        }

        // Generate AI insights
        $insights = $this->generateDropOffInsights($stageMetrics, $dropOffPoints);

        return [
            'stage_metrics' => $stageMetrics,
            'drop_off_points' => $dropOffPoints,
            'ai_insights' => $insights,
            'recommendations' => $this->generateRecommendations($stageMetrics, $dropOffPoints)
        ];
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
     * Get conversion rate for a specific stage
     */
    protected function getStageConversionRate(string $stage, array $journeyData)
    {
        $stages = array_keys($journeyData['funnel_stages']);
        $currentIndex = array_search($stage, $stages);
        
        if ($currentIndex === false || $currentIndex === count($stages) - 1) {
            return 0;
        }

        $currentCount = $journeyData['funnel_stages'][$stage];
        $nextStage = $stages[$currentIndex + 1];
        $nextCount = $journeyData['funnel_stages'][$nextStage];

        return $currentCount > 0 ? ($nextCount / $currentCount) * 100 : 0;
    }

    /**
     * Get average time customers spend in a stage
     */
    protected function getAverageTimeInStage(string $stage, string $startDate, string $endDate, int $branchId)
    {
        $stageTransitions = DB::table('customer_journey_logs')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('stage', $stage)
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, entered_at, exited_at)) as avg_time'))
            ->first();

        return $stageTransitions->avg_time ?? 0;
    }

    /**
     * Get retention rate for a specific stage
     */
    protected function getStageRetentionRate(string $stage, string $startDate, string $endDate, int $branchId)
    {
        $totalCustomers = DB::table('customer_journey_logs')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('stage', $stage)
            ->count();

        $retainedCustomers = DB::table('customer_journey_logs')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('stage', $stage)
            ->where('exited_at', '>', now()->subDays(30))
            ->count();

        return $totalCustomers > 0 ? ($retainedCustomers / $totalCustomers) * 100 : 0;
    }

    /**
     * Generate AI insights for drop-off points
     */
    protected function generateDropOffInsights(array $stageMetrics, array $dropOffPoints)
    {
        $prompt = "Analyze these customer journey metrics and provide insights:\n" .
                 "Stage Metrics: " . json_encode($stageMetrics) . "\n" .
                 "Drop-off Points: " . json_encode($dropOffPoints) . "\n" .
                 "Please provide:\n" .
                 "1. Key drop-off points and their potential causes\n" .
                 "2. Stage-specific insights and opportunities\n" .
                 "3. Customer behavior patterns\n" .
                 "4. Strategic recommendations for improvement";

        return $this->openAIService->generateText($prompt);
    }

    /**
     * Generate recommendations based on metrics
     */
    protected function generateRecommendations(array $stageMetrics, array $dropOffPoints)
    {
        $recommendations = [];

        foreach ($stageMetrics as $stage => $metrics) {
            if ($metrics['conversion_rate'] < 50) {
                $recommendations[] = [
                    'stage' => $stage,
                    'type' => 'conversion',
                    'priority' => 'high',
                    'suggestion' => "Improve conversion rate from {$stage} stage",
                    'action_items' => $this->getActionItemsForStage($stage, $metrics)
                ];
            }

            if ($metrics['retention_rate'] < 70) {
                $recommendations[] = [
                    'stage' => $stage,
                    'type' => 'retention',
                    'priority' => 'medium',
                    'suggestion' => "Enhance retention in {$stage} stage",
                    'action_items' => $this->getRetentionActionItems($stage, $metrics)
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Get specific action items for a stage
     */
    protected function getActionItemsForStage(string $stage, array $metrics)
    {
        $actions = [];
        
        switch ($stage) {
            case 'new_visitors':
                $actions = [
                    'Implement personalized welcome messages',
                    'Create targeted onboarding campaigns',
                    'Optimize first-time user experience'
                ];
                break;
            case 'returning_visitors':
                $actions = [
                    'Send re-engagement emails',
                    'Offer special promotions for returning customers',
                    'Improve product recommendations'
                ];
                break;
            case 'first_time_buyers':
                $actions = [
                    'Send thank you emails with next steps',
                    'Provide product usage guides',
                    'Offer post-purchase support'
                ];
                break;
            case 'repeat_buyers':
                $actions = [
                    'Implement loyalty program features',
                    'Create exclusive offers for repeat customers',
                    'Develop customer feedback programs'
                ];
                break;
        }

        return $actions;
    }

    /**
     * Get retention-specific action items
     */
    protected function getRetentionActionItems(string $stage, array $metrics)
    {
        $actions = [];
        
        switch ($stage) {
            case 'new_visitors':
                $actions = [
                    'Improve website engagement metrics',
                    'Enhance content relevance',
                    'Optimize call-to-action placement'
                ];
                break;
            case 'returning_visitors':
                $actions = [
                    'Implement personalized content recommendations',
                    'Create targeted email campaigns',
                    'Develop social proof elements'
                ];
                break;
            case 'first_time_buyers':
                $actions = [
                    'Send follow-up satisfaction surveys',
                    'Provide product usage tips',
                    'Offer complementary product suggestions'
                ];
                break;
            case 'repeat_buyers':
                $actions = [
                    'Create VIP customer programs',
                    'Implement referral rewards',
                    'Develop exclusive content access'
                ];
                break;
        }

        return $actions;
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
     * Get first time buyers count
     */
    protected function getFirstTimeBuyers(string $startDate, string $endDate, int $branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) = 1')
            ->count();
    }

    /**
     * Get repeat buyers count
     */
    protected function getRepeatBuyers(string $startDate, string $endDate, int $branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) BETWEEN 2 AND 4')
            ->count();
    }

    /**
     * Get loyal customers count
     */
    protected function getLoyalCustomers(string $startDate, string $endDate, int $branchId)
    {
        return Order::where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNull('deleted_at')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) >= 5')
            ->count();
    }
} 