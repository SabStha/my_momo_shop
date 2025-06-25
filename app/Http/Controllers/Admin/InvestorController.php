<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Investor;
use App\Models\InvestorInvestment;
use App\Models\InvestorPayout;
use App\Models\InvestorReport;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvestorController extends Controller
{
    public function index()
    {
        $investors = Investor::with(['investments.branch', 'payouts'])
            ->withCount(['investments', 'payouts'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalInvestors = $investors->count();
        $activeInvestors = $investors->where('status', 'active')->count();
        $totalInvestment = $investors->sum(function($investor) {
            return $investor->investments->sum('investment_amount');
        });
        $totalPayouts = $investors->sum(function($investor) {
            return $investor->payouts->sum('amount');
        });

        return view('admin.investors.index', compact(
            'investors',
            'totalInvestors',
            'activeInvestors',
            'totalInvestment',
            'totalPayouts'
        ));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.investors.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:investors,email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'company_registration' => 'nullable|string|max:255',
            'investment_type' => 'required|in:individual,corporate,partnership',
            'investment_amount' => 'required|numeric|min:0',
            'ownership_percentage' => 'required|numeric|min:0|max:100',
            'investment_date' => 'required|date',
            'status' => 'required|in:active,inactive,pending',
            'notes' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'branch_ownership_percentage' => 'required|numeric|min:0|max:100',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create user account for the investor
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']), // Use custom password
        ]);

        // Assign investor role
        $user->assignRole('investor');

        // Create the investor
        $investor = Investor::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'company_name' => $validated['company_name'],
            'company_registration' => $validated['company_registration'],
            'investment_type' => $validated['investment_type'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'user_id' => $user->id,
        ]);

        // Create the investment record
        $investment = InvestorInvestment::create([
            'investor_id' => $investor->id,
            'branch_id' => $validated['branch_id'],
            'investment_amount' => $validated['investment_amount'],
            'ownership_percentage' => $validated['branch_ownership_percentage'],
            'investment_date' => $validated['investment_date'],
            'status' => 'active',
            'investment_type' => 'equity',
            'risk_level' => 'medium',
            'payment_frequency' => 'monthly',
            'approved_by' => auth()->id(),
            'approval_date' => now(),
        ]);

        return redirect()->route('admin.investors.index')
            ->with('success', 'Investor created successfully. Login credentials: Email: ' . $validated['email'] . ', Password: [Set by admin]');
    }

    public function show(Investor $investor)
    {
        $investor->load(['investments.branch', 'payouts.investment', 'reports']);
        
        // Calculate metrics
        $totalInvestment = $investor->investments->sum('investment_amount');
        $totalPayouts = $investor->payouts->sum('amount');
        $currentValue = $investor->investments->sum('current_value');
        $roi = $totalInvestment > 0 ? (($totalPayouts - $totalInvestment) / $totalInvestment) * 100 : 0;
        
        // Monthly payout calculation
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
            ->take(10)
            ->get();
        
        // Performance over time
        $monthlyPerformance = $this->getMonthlyPerformance($investor->id);

        return view('admin.investors.show', compact(
            'investor',
            'totalInvestment',
            'totalPayouts',
            'currentValue',
            'roi',
            'monthlyPayout',
            'branchInvestments',
            'recentPayouts',
            'monthlyPerformance'
        ));
    }

    public function edit(Investor $investor)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.investors.edit', compact('investor', 'branches'));
    }

    public function update(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:investors,email,' . $investor->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'company_name' => 'nullable|string|max:255',
            'investment_type' => 'required|in:individual,corporate,angel,venture_capital',
            'total_investment_amount' => 'required|numeric|min:0',
            'investment_date' => 'required|date',
            'status' => 'required|in:active,inactive,pending',
            'notes' => 'nullable|string',
            'tax_id' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'risk_profile' => 'required|in:conservative,moderate,aggressive',
            'investment_horizon' => 'required|in:short_term,medium_term,long_term',
            'preferred_communication' => 'nullable|string|max:255',
            'bank_details' => 'nullable|array',
            'social_media' => 'nullable|array',
        ]);

        $investor->update($validated);

        return redirect()->route('admin.investors.index')
            ->with('success', 'Investor updated successfully');
    }

    public function destroy(Investor $investor)
    {
        $investor->delete();
        return redirect()->route('admin.investors.index')
            ->with('success', 'Investor deleted successfully');
    }

    // Investment Management
    public function investments(Investor $investor)
    {
        $investments = $investor->investments()
            ->with('branch')
            ->orderBy('investment_date', 'desc')
            ->get();

        $branches = Branch::where('is_active', true)->get();

        return view('admin.investors.investments', compact('investor', 'investments', 'branches'));
    }

    public function storeInvestment(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'investment_amount' => 'required|numeric|min:0',
            'ownership_percentage' => 'required|numeric|min:0|max:100',
            'investment_date' => 'required|date',
            'status' => 'required|in:active,inactive,pending,sold',
            'investment_type' => 'required|in:equity,debt,convertible_note',
            'expected_return' => 'nullable|numeric|min:0|max:100',
            'risk_level' => 'required|in:low,medium,high',
            'payment_frequency' => 'required|in:monthly,quarterly,annually',
            'maturity_date' => 'nullable|date|after:investment_date',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|array',
            'exit_strategy' => 'nullable|string',
        ]);

        $validated['investor_id'] = $investor->id;
        $validated['approved_by'] = auth()->id();
        $validated['approval_date'] = now();

        InvestorInvestment::create($validated);

        return redirect()->route('admin.investors.investments', $investor)
            ->with('success', 'Investment added successfully');
    }

    // Payout Management
    public function payouts(Investor $investor)
    {
        $payouts = $investor->payouts()
            ->with(['branch', 'investment'])
            ->orderBy('payout_date', 'desc')
            ->get();

        $investments = $investor->investments()->where('status', 'active')->get();
        $branches = Branch::where('is_active', true)->get();

        return view('admin.investors.payouts', compact('investor', 'payouts', 'investments', 'branches'));
    }

    public function storePayout(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'investment_id' => 'nullable|exists:investor_investments,id',
            'branch_id' => 'required|exists:branches,id',
            'amount' => 'required|numeric|min:0',
            'payout_date' => 'required|date',
            'payout_type' => 'required|in:dividend,interest,principal,profit_share',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start',
            'status' => 'required|in:pending,processed,paid,failed',
            'payment_method' => 'required|in:bank_transfer,check,cash,digital_wallet',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'tax_amount' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'exchange_rate' => 'nullable|numeric|min:0',
        ]);

        $validated['investor_id'] = $investor->id;
        $validated['net_amount'] = $validated['amount'] - ($validated['tax_amount'] ?? 0);
        $validated['processed_by'] = auth()->id();
        $validated['processed_at'] = now();

        InvestorPayout::create($validated);

        return redirect()->route('admin.investors.payouts', $investor)
            ->with('success', 'Payout created successfully');
    }

    // Report Management
    public function reports(Investor $investor)
    {
        $reports = $investor->reports()
            ->orderBy('generated_at', 'desc')
            ->get();

        return view('admin.investors.reports', compact('investor', 'reports'));
    }

    public function generateReport(Request $request, Investor $investor)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:monthly,quarterly,annual,performance,payout',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'title' => 'required|string|max:255',
        ]);

        // Generate report content based on type
        $content = $this->generateReportContent($investor, $validated);
        $metricsData = $this->generateMetricsData($investor, $validated['period_start'], $validated['period_end']);
        $chartsData = $this->generateChartsData($investor, $validated['period_start'], $validated['period_end']);

        $report = InvestorReport::create([
            'investor_id' => $investor->id,
            'report_type' => $validated['report_type'],
            'title' => $validated['title'],
            'content' => $content,
            'generated_at' => now(),
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'status' => 'draft',
            'metrics_data' => $metricsData,
            'charts_data' => $chartsData,
        ]);

        return redirect()->route('admin.investors.reports', $investor)
            ->with('success', 'Report generated successfully');
    }

    // Dashboard for specific investor
    public function dashboard(Investor $investor)
    {
        $investor->load(['investments.branch', 'payouts.investment']);
        
        // Financial metrics
        $totalInvestment = $investor->investments->sum('investment_amount');
        $totalPayouts = $investor->payouts->sum('amount');
        $currentValue = $investor->investments->sum('current_value');
        $roi = $totalInvestment > 0 ? (($totalPayouts - $totalInvestment) / $totalInvestment) * 100 : 0;
        
        // Monthly metrics
        $monthlyPayout = $investor->monthly_payout;
        $monthlyRevenue = $this->getMonthlyRevenue($investor->id);
        
        // Branch performance
        $branchPerformance = $this->getBranchPerformance($investor->id);
        
        // Recent activity
        $recentPayouts = $investor->payouts()
            ->with('branch')
            ->orderBy('payout_date', 'desc')
            ->take(5)
            ->get();
        
        $recentInvestments = $investor->investments()
            ->with('branch')
            ->orderBy('investment_date', 'desc')
            ->take(5)
            ->get();

        return view('admin.investors.dashboard', compact(
            'investor',
            'totalInvestment',
            'totalPayouts',
            'currentValue',
            'roi',
            'monthlyPayout',
            'monthlyRevenue',
            'branchPerformance',
            'recentPayouts',
            'recentInvestments'
        ));
    }

    // Helper methods
    private function getMonthlyPerformance($investorId)
    {
        return DB::table('investor_payouts')
            ->where('investor_id', $investorId)
            ->select(
                DB::raw('YEAR(payout_date) as year'),
                DB::raw('MONTH(payout_date) as month'),
                DB::raw('SUM(amount) as total_payouts'),
                DB::raw('COUNT(*) as payout_count')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }

    private function getMonthlyRevenue($investorId)
    {
        $investments = InvestorInvestment::where('investor_id', $investorId)
            ->where('status', 'active')
            ->with('branch')
            ->get();

        $totalRevenue = 0;
        foreach ($investments as $investment) {
            $branchRevenue = Order::where('branch_id', $investment->branch_id)
                ->where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('total_amount');
            
            $investorShare = $branchRevenue * ($investment->ownership_percentage / 100);
            $totalRevenue += $investorShare;
        }

        return $totalRevenue;
    }

    private function getBranchPerformance($investorId)
    {
        return DB::table('investor_investments as ii')
            ->join('branches as b', 'ii.branch_id', '=', 'b.id')
            ->leftJoin('orders as o', function($join) {
                $join->on('b.id', '=', 'o.branch_id')
                     ->where('o.status', '=', 'completed')
                     ->whereMonth('o.created_at', now()->month);
            })
            ->where('ii.investor_id', $investorId)
            ->where('ii.status', 'active')
            ->select(
                'b.name as branch_name',
                'ii.ownership_percentage',
                'ii.investment_amount',
                DB::raw('COALESCE(SUM(o.total_amount), 0) as monthly_revenue'),
                DB::raw('COALESCE(SUM(o.total_amount), 0) * (ii.ownership_percentage / 100) as investor_share')
            )
            ->groupBy('b.id', 'b.name', 'ii.ownership_percentage', 'ii.investment_amount')
            ->get();
    }

    private function generateReportContent($investor, $data)
    {
        // Generate comprehensive report content
        $content = "Performance Report for {$investor->name}\n\n";
        $content .= "Period: {$data['period_start']} to {$data['period_end']}\n\n";
        
        // Add investment summary
        $totalInvestment = $investor->investments->sum('investment_amount');
        $totalPayouts = $investor->payouts()
            ->whereBetween('payout_date', [$data['period_start'], $data['period_end']])
            ->sum('amount');
        
        $content .= "Investment Summary:\n";
        $content .= "- Total Investment: Rs " . number_format($totalInvestment, 2) . "\n";
        $content .= "- Period Payouts: Rs " . number_format($totalPayouts, 2) . "\n";
        $content .= "- ROI: " . number_format(($totalInvestment > 0 ? ($totalPayouts / $totalInvestment - 1) * 100 : 0), 2) . "%\n\n";
        
        return $content;
    }

    private function generateMetricsData($investor, $startDate, $endDate)
    {
        return [
            'total_investment' => $investor->investments->sum('investment_amount'),
            'total_payouts' => $investor->payouts()
                ->whereBetween('payout_date', [$startDate, $endDate])
                ->sum('amount'),
            'active_investments' => $investor->investments()->where('status', 'active')->count(),
            'branch_count' => $investor->investments()->distinct('branch_id')->count(),
        ];
    }

    private function generateChartsData($investor, $startDate, $endDate)
    {
        // Generate chart data for the report
        $monthlyPayouts = $investor->payouts()
            ->whereBetween('payout_date', [$startDate, $endDate])
            ->select(
                DB::raw('DATE_FORMAT(payout_date, "%Y-%m") as month'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'monthly_payouts' => $monthlyPayouts,
            'branch_distribution' => $investor->investments()
                ->with('branch')
                ->get()
                ->groupBy('branch.name')
                ->map(function($investments) {
                    return $investments->sum('investment_amount');
                })
        ];
    }
} 