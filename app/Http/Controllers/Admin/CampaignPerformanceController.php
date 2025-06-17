<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Campaign;
use App\Models\CampaignTrigger;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CampaignPerformanceController extends Controller
{
    public function index()
    {
        $branch = Branch::find(session('selected_branch_id'));
        if (!$branch) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Please select a branch first.');
        }

        // Get campaign performance metrics
        $campaigns = Campaign::where('branch_id', $branch->id)
            ->with(['triggers', 'customers'])
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'type' => $campaign->type,
                    'status' => $campaign->status,
                    'total_customers' => $campaign->customers->count(),
                    'total_triggers' => $campaign->triggers->count(),
                    'redemptions' => $this->calculateRedemptions($campaign),
                    'open_rate' => $this->calculateOpenRate($campaign),
                    'engagement_rate' => $this->calculateEngagementRate($campaign),
                    'roi' => $this->calculateROI($campaign),
                    'created_at' => $campaign->created_at,
                ];
            });

        // Get overall metrics
        $overallMetrics = [
            'total_campaigns' => $campaigns->count(),
            'total_customers_reached' => $campaigns->sum('total_customers'),
            'total_redemptions' => $campaigns->sum('redemptions'),
            'average_open_rate' => $campaigns->avg('open_rate'),
            'average_engagement_rate' => $campaigns->avg('engagement_rate'),
            'average_roi' => $campaigns->avg('roi'),
        ];

        return view('admin.campaigns.performance', compact('campaigns', 'overallMetrics'));
    }

    private function calculateRedemptions($campaign)
    {
        return $campaign->triggers()
            ->where('status', 'completed')
            ->where('action_taken', 'redeemed')
            ->count();
    }

    private function calculateOpenRate($campaign)
    {
        $totalSent = $campaign->triggers()->where('status', 'completed')->count();
        if ($totalSent === 0) return 0;

        $totalOpened = $campaign->triggers()
            ->where('status', 'completed')
            ->where('opened_at', '!=', null)
            ->count();

        return round(($totalOpened / $totalSent) * 100, 2);
    }

    private function calculateEngagementRate($campaign)
    {
        $totalSent = $campaign->triggers()->where('status', 'completed')->count();
        if ($totalSent === 0) return 0;

        $totalEngaged = $campaign->triggers()
            ->where('status', 'completed')
            ->where(function ($query) {
                $query->where('clicked_at', '!=', null)
                    ->orWhere('action_taken', '!=', null);
            })
            ->count();

        return round(($totalEngaged / $totalSent) * 100, 2);
    }

    private function calculateROI($campaign)
    {
        $cost = $campaign->cost ?? 0;
        if ($cost === 0) return 0;

        $revenue = $campaign->triggers()
            ->where('status', 'completed')
            ->where('action_taken', 'redeemed')
            ->sum('revenue_generated');

        return round((($revenue - $cost) / $cost) * 100, 2);
    }

    public function show(Campaign $campaign)
    {
        $branch = Branch::find(session('selected_branch_id'));
        if (!$branch) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Please select a branch first.');
        }

        // Get detailed campaign metrics
        $metrics = [
            'basic' => [
                'name' => $campaign->name,
                'type' => $campaign->type,
                'status' => $campaign->status,
                'start_date' => $campaign->start_date,
                'end_date' => $campaign->end_date,
                'total_customers' => $campaign->customers->count(),
                'total_triggers' => $campaign->triggers->count(),
            ],
            'performance' => [
                'redemptions' => $this->calculateRedemptions($campaign),
                'open_rate' => $this->calculateOpenRate($campaign),
                'engagement_rate' => $this->calculateEngagementRate($campaign),
                'roi' => $this->calculateROI($campaign),
            ],
            'timeline' => $this->getCampaignTimeline($campaign),
            'customer_segments' => $this->getCustomerSegmentMetrics($campaign),
        ];

        return view('admin.campaigns.performance-details', compact('campaign', 'metrics'));
    }

    private function getCampaignTimeline($campaign)
    {
        return $campaign->triggers()
            ->where('status', 'completed')
            ->orderBy('created_at')
            ->get()
            ->map(function ($trigger) {
                return [
                    'date' => $trigger->created_at,
                    'action' => $trigger->action_taken,
                    'revenue' => $trigger->revenue_generated,
                ];
            });
    }

    private function getCustomerSegmentMetrics($campaign)
    {
        return $campaign->customers()
            ->with('segment')
            ->get()
            ->groupBy('segment.name')
            ->map(function ($customers) {
                return [
                    'count' => $customers->count(),
                    'redemptions' => $customers->sum(function ($customer) {
                        return $customer->campaignTriggers()
                            ->where('action_taken', 'redeemed')
                            ->count();
                    }),
                    'revenue' => $customers->sum(function ($customer) {
                        return $customer->campaignTriggers()
                            ->where('action_taken', 'redeemed')
                            ->sum('revenue_generated');
                    }),
                ];
            });
    }
} 