<?php

namespace App\Http\Controllers\Investor;

use App\Http\Controllers\Controller;
use App\Models\Investor;
use App\Models\InvestorPayout;
use App\Models\Order;
use App\Models\BranchUpdate;
use App\Models\RiskAlert;
use App\Models\ImpactStat;
use App\Models\InvestorReferral;
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

    /**
     * Redirect admin to investor management page
     */
    private function showInvestorList()
    {
        return redirect()->route('admin.investors.index')
            ->with('info', 'Please select an investor to view their dashboard.');
    }

    public function dashboard($investorId = null)
    {
        $user = Auth::user();
        
        // If admin is accessing without investor ID, show investor list
        if ($user->hasRole('admin') && !$investorId) {
            return $this->showInvestorList();
        }
        
        // Get the investor to display
        if ($investorId && $user->hasRole('admin')) {
            // Admin viewing a specific investor
            $investor = Investor::findOrFail($investorId);
        } else {
            // Regular investor viewing their own dashboard
            $investor = $user->investor;
            
            if (!$investor) {
                return redirect()->route('home')->with('error', 'No investor account found. Please contact administration.');
            }
        }

        // Load relationships
        $investor->load(['investments.branch', 'payouts.investment']);

        // Calculate metrics
        $totalInvestment = $investor->investments->sum('investment_amount');
        $totalPayouts = $investor->payouts->sum('amount');
        $currentValue = $totalInvestment; // Use investment amount as current value
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
        
        // Allow both admin and investor users to access this page
        
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('home')->with('info', 'No investor account found.');
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
        
        // Allow both admin and investor users to access this page
        
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('home')->with('info', 'No investor account found.');
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
        
        // Allow both admin and investor users to access this page
        
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('home')->with('info', 'No investor account found.');
        }

        $reports = $investor->reports()
            ->orderBy('created_at', 'desc')
            ->get();

        return view('investor.reports', compact('investor', 'reports'));
    }

    public function profile()
    {
        $user = Auth::user();
        
        // Allow both admin and investor users to access this page
        
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('home')->with('info', 'No investor account found.');
        }

        return view('investor.profile', compact('investor'));
    }


    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        // Allow both admin and investor users to access this page
        
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('home')->with('info', 'No investor account found.');
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
        $branchIds = $investor->investments->pluck('branch_id');
        
        // Get real branch updates from database
        $updates = BranchUpdate::whereIn('branch_id', $branchIds)
            ->published()
            ->recent(30)
            ->orderBy('published_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($update) {
                return [
                    'branch_name' => $update->branch->name,
                    'date' => $update->published_at->format('M d, Y'),
                    'type' => $update->type,
                    'title' => $update->title,
                    'content' => $update->content,
                    'icon' => $update->icon ?? '📢'
                ];
            })
            ->toArray();
        
        return $updates;
    }

    // 4. Risk/Alert Section
    private function getRiskAlerts($investorId)
    {
        $investor = Investor::find($investorId);
        $branchIds = $investor->investments->pluck('branch_id');
        
        // Get real risk alerts from database
        $alerts = RiskAlert::whereIn('branch_id', $branchIds)
            ->active()
            ->orderBy('severity', 'desc')
            ->orderBy('detected_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($alert) {
                return [
                    'type' => $alert->category,
                    'title' => $alert->title,
                    'message' => $alert->message,
                    'severity' => $alert->severity,
                    'date' => $alert->detected_at ? $alert->detected_at->format('M d, Y') : now()->format('M d, Y')
                ];
            })
            ->toArray();
        
        return $alerts;
    }

    // 5. Impact Tracker - Social Contribution Stats
    private function getImpactStats($investorId)
    {
        $investor = Investor::find($investorId);
        $branchIds = $investor->investments->pluck('branch_id');
        
        // Get current month impact stats
        $currentMonth = ImpactStat::whereIn('branch_id', $branchIds)
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->byPeriodType('monthly')
            ->get();
        
        // If no data for current month, return zeros
        if ($currentMonth->isEmpty()) {
            return [
                'monthly_donation' => 0,
                'donation_percentage' => 0,
                'plates_funded' => 0,
                'dogs_saved' => 0,
                'total_sales' => 0
            ];
        }
        
        // Aggregate stats from all branches
        $totalDonation = $currentMonth->sum('donation_amount');
        $totalPlates = $currentMonth->sum('plates_funded');
        $totalDogs = $currentMonth->sum('dogs_saved');
        $totalSales = $currentMonth->sum('total_sales');
        $avgPercentage = $currentMonth->avg('donation_percentage');
        
        return [
            'monthly_donation' => $totalDonation,
            'donation_percentage' => $avgPercentage ?? 0,
            'plates_funded' => $totalPlates,
            'dogs_saved' => $totalDogs,
            'total_sales' => $totalSales
        ];
    }

    // 6. Referral and Reinvestment Stats
    private function getReferralStats($investorId)
    {
        $investor = Investor::find($investorId);
        
        // Get or create unique referral code for this investor
        $existingReferral = InvestorReferral::where('referrer_investor_id', $investorId)->first();
        $referralCode = $existingReferral ? $existingReferral->referral_code : InvestorReferral::generateUniqueCode($investorId);
        
        // Generate referral link
        $referralLink = route('register') . '?ref=' . $referralCode;
        
        // Get real referral stats from database
        $stats = InvestorReferral::getReferralStats($investorId);
        
        return [
            'referral_link' => $referralLink,
            'total_referrals' => $stats['total_referrals'],
            'successful_referrals' => $stats['successful_referrals'],
            'referral_earnings' => $stats['total_earnings'],
        ];
    }

    /**
     * Generate and download investor statement
     */
    public function generateStatement(Request $request)
    {
        $user = Auth::user();
        $investor = $user->investor;

        if (!$investor) {
            return redirect()->route('home')->with('error', 'No investor account found.');
        }

        // Load all necessary data
        $investor->load(['investments.branch', 'payouts.investment']);

        // Calculate metrics
        $totalInvestment = $investor->investments->sum('investment_amount');
        $totalPayouts = $investor->payouts->sum('amount');
        $roi = $totalInvestment > 0 ? (($totalPayouts - $totalInvestment) / $totalInvestment) * 100 : 0;

        // Get date range (current year)
        $startDate = now()->startOfYear();
        $endDate = now();

        // Get monthly payouts
        $monthlyPayouts = $investor->payouts()
            ->whereBetween('payout_date', [$startDate, $endDate])
            ->orderBy('payout_date', 'desc')
            ->get()
            ->groupBy(function($payout) {
                return $payout->payout_date->format('F Y');
            });

        // Generate PDF or view
        return view('investor.statement', compact(
            'investor',
            'totalInvestment',
            'totalPayouts',
            'roi',
            'monthlyPayouts',
            'startDate',
            'endDate'
        ));
    }
}
