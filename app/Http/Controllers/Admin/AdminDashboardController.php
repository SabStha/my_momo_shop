<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ActivityLog;
use App\Models\Rule;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\CampaignRedemption;
use App\Models\Activity;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $branches = Branch::all();
        $selectedBranchId = session('selected_branch_id');

        if ($selectedBranchId) {
            $totalCustomers = Customer::where('branch_id', $selectedBranchId)->count();
            $totalOrders = Order::where('branch_id', $selectedBranchId)
                ->whereMonth('created_at', now()->month)
                ->count();
            $totalRevenue = Order::where('branch_id', $selectedBranchId)
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount');
            $activeCampaigns = Campaign::where('branch_id', $selectedBranchId)
                ->where('status', 'active')
                ->count();
            $rules = Rule::where('branch_id', $selectedBranchId)
                ->orderBy('priority')
                ->get();
            $campaigns = Campaign::where('branch_id', $selectedBranchId)
                ->orderBy('created_at', 'desc')
                ->get();

            // Get sales trend data
            $salesTrend = Order::where('branch_id', $selectedBranchId)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, SUM(total_amount) as amount')
                ->groupBy('date')
                ->get();

            // Get campaign metrics
            $campaignMetrics = [
                'total_redemptions' => 0,
                'average_open_rate' => 0,
                'average_engagement_rate' => 0,
                'average_roi' => 0
            ];

            return view('admin.dashboard', compact(
                'branches',
                'totalCustomers',
                'totalOrders',
                'totalRevenue',
                'activeCampaigns',
                'rules',
                'campaigns',
                'salesTrend',
                'campaignMetrics'
            ));
        }

        return view('admin.dashboard', compact('branches'));
    }

    private function getCampaignMetrics($branch)
    {
        $campaigns = Campaign::where('branch_id', $branch->id)->get();

        $totalRedemptions = 0;
        $totalOpenRate = 0;
        $totalEngagementRate = 0;
        $totalROI = 0;
        $campaignCount = $campaigns->count();

        foreach ($campaigns as $campaign) {
            // Calculate redemptions
            $redemptions = $campaign->triggers()
                ->where('status', 'completed')
                ->where('action_taken', 'redeemed')
                ->count();
            $totalRedemptions += $redemptions;

            // Calculate open rate
            $totalSent = $campaign->triggers()->where('status', 'completed')->count();
            if ($totalSent > 0) {
                $totalOpened = $campaign->triggers()
                    ->where('status', 'completed')
                    ->where('opened_at', '!=', null)
                    ->count();
                $totalOpenRate += ($totalOpened / $totalSent) * 100;
            }

            // Calculate engagement rate
            if ($totalSent > 0) {
                $totalEngaged = $campaign->triggers()
                    ->where('status', 'completed')
                    ->where(function ($query) {
                        $query->where('clicked_at', '!=', null)
                            ->orWhere('action_taken', '!=', null);
                    })
                    ->count();
                $totalEngagementRate += ($totalEngaged / $totalSent) * 100;
            }

            // Calculate ROI
            $cost = $campaign->cost ?? 0;
            if ($cost > 0) {
                $revenue = $campaign->triggers()
                    ->where('status', 'completed')
                    ->where('action_taken', 'redeemed')
                    ->sum('revenue_generated');
                $totalROI += (($revenue - $cost) / $cost) * 100;
            }
        }

        return [
            'total_redemptions' => $totalRedemptions,
            'average_open_rate' => $campaignCount > 0 ? $totalOpenRate / $campaignCount : 0,
            'average_engagement_rate' => $campaignCount > 0 ? $totalEngagementRate / $campaignCount : 0,
            'average_roi' => $campaignCount > 0 ? $totalROI / $campaignCount : 0,
        ];
    }
} 