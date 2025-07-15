<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Investor;
use App\Models\InvestorPayout;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|investor');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('login')->with('error', 'Investor account not found.');
        }

        // Load relationships
        $investor->load(['investments.branch', 'payouts.investment']);

        // Calculate metrics
        $totalInvestment = $investor->investments->sum('investment_amount');
        $totalPayouts = $investor->payouts->sum('amount');
        $currentValue = $investor->investments->sum('current_value');
        $roi = $totalInvestment > 0 ? (($totalPayouts - $totalInvestment) / $totalInvestment) * 100 : 0;
        $monthlyPayout = $investor->monthly_payout;

        // Investment distribution by branch
        $branchInvestments = $investor->investments()
            ->with('branch')
            ->get()
            ->groupBy('branch.name');

        // Recent payouts
        $recentPayouts = $investor->payouts()
            ->with('branch')
            ->orderBy('payout_date', 'desc')
            ->take(5)
            ->get();

        // Monthly performance data
        $monthlyPerformance = $this->getMonthlyPerformance($investor->id);

        // Recent investments
        $recentInvestments = $investor->investments()
            ->with('branch')
            ->orderBy('investment_date', 'desc')
            ->take(5)
            ->get();

        // NEW FEATURES
        
        // 1. Live Branch Performance Feed
        $liveBranchPerformance = $this->getLiveBranchPerformance($investor->id);
        
        // 2. Investment Timeline/Milestone Tracker
        $investmentTimeline = $this->getInvestmentTimeline($investor->id);
        
        // 3. Branch-Specific Updates (Mini Blog/Feed)
        $branchUpdates = $this->getBranchUpdates($investor->id);
        
        // 4. Risk/Alert Section
        $riskAlerts = $this->getRiskAlerts($investor->id);
        
        // 5. Impact Tracker - Social Contribution Stats
        $impactStats = $this->getImpactStats($investor->id);
        
        // 6. Referral and Reinvestment Stats
        $referralStats = $this->getReferralStats($investor->id);

        return view('investor.dashboard', compact(
            'investor',
            'totalInvestment',
            'totalPayouts',
            'currentValue',
            'roi',
            'monthlyPayout',
            'branchInvestments',
            'recentPayouts',
            'monthlyPerformance',
            'recentInvestments',
            'liveBranchPerformance',
            'investmentTimeline',
            'branchUpdates',
            'riskAlerts',
            'impactStats',
            'referralStats'
        ));
    }

    public function investments()
    {
        $user = Auth::user();
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('login')->with('error', 'Investor account not found.');
        }

        $investments = $investor->investments()
            ->with('branch')
            ->orderBy('investment_date', 'desc')
            ->get();

        return view('investor.investments', compact('investor', 'investments'));
    }

    public function payouts()
    {
        $user = Auth::user();
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('login')->with('error', 'Investor account not found.');
        }

        $payouts = $investor->payouts()
            ->with(['branch', 'investment'])
            ->orderBy('payout_date', 'desc')
            ->get();

        return view('investor.payouts', compact('investor', 'payouts'));
    }

    public function reports()
    {
        $user = Auth::user();
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('login')->with('error', 'Investor account not found.');
        }

        $reports = $investor->reports()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('investor.reports', compact('investor', 'reports'));
    }

    public function profile()
    {
        $user = Auth::user();
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('login')->with('error', 'Investor account not found.');
        }

        return view('investor.profile', compact('investor'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('login')->with('error', 'Investor account not found.');
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $investor->update($validated);

        return redirect()->route('investor.profile')
            ->with('success', 'Profile updated successfully');
    }

    private function getMonthlyPerformance($investorId)
    {
        $months = [];
        $performance = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');

            // Calculate monthly payout
            $monthlyPayout = InvestorPayout::where('investor_id', $investorId)
                ->whereYear('payout_date', $date->year)
                ->whereMonth('payout_date', $date->month)
                ->sum('amount');

            $performance[] = $monthlyPayout;
        }

        return [
            'months' => $months,
            'performance' => $performance
        ];
    }

    // 1. Live Branch Performance Feed
    private function getLiveBranchPerformance($investorId)
    {
        $investor = Investor::find($investorId);
        $branchPerformance = [];

        foreach ($investor->investments as $investment) {
            $branch = $investment->branch;
            
            // Get today's sales (simulated - replace with actual data)
            $todaySales = Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today())
                ->sum('total_amount');
            
            // Get yesterday's sales for comparison
            $yesterdaySales = Order::where('branch_id', $branch->id)
                ->whereDate('created_at', today()->subDay())
                ->sum('total_amount');
            
            // Calculate break-even point (simplified - 50% of average daily sales)
            $avgDailySales = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [now()->subDays(30), now()])
                ->avg('total_amount') ?? 10000;
            
            $breakEvenPoint = $avgDailySales * 0.5;
            
            // Determine performance status
            if ($todaySales >= $breakEvenPoint * 1.2) {
                $status = 'green'; // Above break-even
            } elseif ($todaySales >= $breakEvenPoint * 0.8) {
                $status = 'yellow'; // Near break-even
            } else {
                $status = 'red'; // Below break-even
            }
            
            $branchPerformance[] = [
                'branch_name' => $branch->name,
                'today_sales' => $todaySales,
                'yesterday_sales' => $yesterdaySales,
                'break_even_point' => $breakEvenPoint,
                'status' => $status,
                'change_percentage' => $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0
            ];
        }

        return $branchPerformance;
    }

    // 2. Investment Timeline/Milestone Tracker
    private function getInvestmentTimeline($investorId)
    {
        $investor = Investor::find($investorId);
        $timeline = [];
        
        foreach ($investor->investments as $investment) {
            $branch = $investment->branch;
            
            // Calculate milestones based on investment date
            $investmentDate = Carbon::parse($investment->investment_date);
            $kitchenSetupDate = $investmentDate->copy()->addDays(30);
            $launchDate = $investmentDate->copy()->addDays(60);
            $breakEvenDate = $investmentDate->copy()->addDays(120);
            $roiMilestoneDate = $investmentDate->copy()->addDays(180);
            $expansionDate = $investmentDate->copy()->addDays(365);
            
            $timeline[] = [
                'branch_name' => $branch->name,
                'investment_amount' => $investment->investment_amount,
                'milestones' => [
                    [
                        'title' => 'Investment Made',
                        'date' => $investmentDate->format('M d, Y'),
                        'status' => 'completed',
                        'description' => 'Investment of Rs. ' . number_format($investment->investment_amount) . ' made'
                    ],
                    [
                        'title' => 'Kitchen Setup',
                        'date' => $kitchenSetupDate->format('M d, Y'),
                        'status' => $kitchenSetupDate->isPast() ? 'completed' : 'pending',
                        'description' => 'Kitchen equipment and setup completed'
                    ],
                    [
                        'title' => 'Branch Launch',
                        'date' => $launchDate->format('M d, Y'),
                        'status' => $launchDate->isPast() ? 'completed' : 'pending',
                        'description' => 'Branch officially opened for business'
                    ],
                    [
                        'title' => 'Break-even Achieved',
                        'date' => $breakEvenDate->format('M d, Y'),
                        'status' => $breakEvenDate->isPast() ? 'completed' : 'pending',
                        'description' => 'Branch reached break-even point'
                    ],
                    [
                        'title' => 'ROI Milestone',
                        'date' => $roiMilestoneDate->format('M d, Y'),
                        'status' => $roiMilestoneDate->isPast() ? 'completed' : 'pending',
                        'description' => 'First major ROI milestone reached'
                    ],
                    [
                        'title' => 'Expansion Phase',
                        'date' => $expansionDate->format('M d, Y'),
                        'status' => $expansionDate->isPast() ? 'completed' : 'pending',
                        'description' => 'Branch ready for expansion or new location'
                    ]
                ]
            ];
        }
        
        return $timeline;
    }

    // 3. Branch-Specific Updates (Mini Blog/Feed)
    private function getBranchUpdates($investorId)
    {
        $investor = Investor::find($investorId);
        $updates = [];
        
        foreach ($investor->investments as $investment) {
            $branch = $investment->branch;
            
            // Get recent orders for this branch
            $recentOrders = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [now()->subDays(7), now()])
                ->count();
            
            // Get customer reviews (simulated)
            $recentReviews = [
                'Great food and service!',
                'Best momos in town!',
                'Will definitely come back!',
                'Amazing taste and quality!'
            ];
            
            $updates[] = [
                'branch_name' => $branch->name,
                'date' => now()->format('M d, Y'),
                'type' => 'sales_update',
                'title' => $branch->name . ' hit ' . $recentOrders . '+ orders this week! 🎉',
                'content' => 'Strong performance with excellent customer feedback.',
                'icon' => '📈'
            ];
            
            $updates[] = [
                'branch_name' => $branch->name,
                'date' => now()->subDays(2)->format('M d, Y'),
                'type' => 'promo_update',
                'title' => 'New promo launched this week!',
                'content' => 'Buy 2 Get 1 Free on all steamed momos.',
                'icon' => '🎯'
            ];
            
            $updates[] = [
                'branch_name' => $branch->name,
                'date' => now()->subDays(5)->format('M d, Y'),
                'type' => 'review_highlight',
                'title' => 'Customer Review Highlight',
                'content' => '"' . $recentReviews[array_rand($recentReviews)] . '"',
                'icon' => '⭐'
            ];
        }
        
        // Sort by date (most recent first)
        usort($updates, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return array_slice($updates, 0, 10); // Return latest 10 updates
    }

    // 4. Risk/Alert Section
    private function getRiskAlerts($investorId)
    {
        $investor = Investor::find($investorId);
        $alerts = [];
        
        foreach ($investor->investments as $investment) {
            $branch = $investment->branch;
            
            // Check for low sales (3 weeks)
            $recentSales = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [now()->subWeeks(3), now()])
                ->sum('total_amount');
            
            $avgWeeklySales = $recentSales / 3;
            $historicalAvg = Order::where('branch_id', $branch->id)
                ->whereBetween('created_at', [now()->subWeeks(12), now()->subWeeks(3)])
                ->sum('total_amount') / 9;
            
            if ($avgWeeklySales < $historicalAvg * 0.7) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Low Sales Alert',
                    'message' => $branch->name . ' showing low sales for 3 weeks.',
                    'severity' => 'medium',
                    'date' => now()->format('M d, Y')
                ];
            }
            
            // Check for rising costs (simulated)
            if (rand(1, 10) === 1) { // 10% chance for demo
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Cost Alert',
                    'message' => 'Raw material costs rising – monitoring margin compression.',
                    'severity' => 'low',
                    'date' => now()->format('M d, Y')
                ];
            }
        }
        
        return $alerts;
    }

    // 5. Impact Tracker - Social Contribution Stats
    private function getImpactStats($investorId)
    {
        $investor = Investor::find($investorId);
        
        // Calculate total sales from investor's branches
        $totalSales = 0;
        foreach ($investor->investments as $investment) {
            $branchSales = Order::where('branch_id', $investment->branch_id)
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount');
            $totalSales += $branchSales;
        }
        
        // Assume 2% of sales goes to social causes
        $donationPercentage = 2;
        $monthlyDonation = $totalSales * ($donationPercentage / 100);
        
        // Calculate plates funded (assuming Rs. 50 per plate for animal care)
        $platesFunded = $monthlyDonation / 50;
        
        // Calculate dogs saved (assuming Rs. 1000 per dog)
        $dogsSaved = $monthlyDonation / 1000;
        
        return [
            'monthly_donation' => $monthlyDonation,
            'donation_percentage' => $donationPercentage,
            'plates_funded' => $platesFunded,
            'dogs_saved' => $dogsSaved,
            'total_sales' => $totalSales
        ];
    }

    // 6. Referral and Reinvestment Stats
    private function getReferralStats($investorId)
    {
        $investor = Investor::find($investorId);
        
        // Generate referral link
        $referralLink = route('register') . '?ref=' . $investor->id;
        
        // Simulated referral stats (replace with actual data)
        $referralStats = [
            'referral_link' => $referralLink,
            'total_referrals' => rand(0, 5), // Replace with actual count
            'successful_referrals' => rand(0, 3), // Replace with actual count
            'referral_earnings' => rand(0, 50000), // Replace with actual earnings
            'reinvestment_opportunities' => $investor->monthly_payout > 0 ? 'Available' : 'None',
            'wallet_balance' => rand(0, 100000) // Replace with actual wallet balance
        ];
        
        return $referralStats;
    }
}
