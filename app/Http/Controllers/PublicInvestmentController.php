<?php

namespace App\Http\Controllers;

use App\Models\Investor;
use App\Models\InvestorInvestment;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PublicInvestmentController extends Controller
{
    /**
     * Show the public investment page
     */
    public function index()
    {
        // Get available branches for investment
        $branches = Branch::where('is_active', true)
            ->where('is_main', false) // Exclude main branch
            ->get();
            
        // Get top investors by total investment amount
        $topInvestors = Investor::with(['investments' => function($query) {
                $query->whereIn('status', ['active', 'pending']);
            }])
            ->whereIn('status', ['active', 'pending'])
            ->get()
            ->map(function($investor) {
                $investor->total_invested = $investor->investments->sum('investment_amount');
                $investor->total_payouts = $investor->payouts->sum('amount');
                $investor->roi = $investor->total_invested > 0 ? 
                    (($investor->total_payouts - $investor->total_invested) / $investor->total_invested) * 100 : 0;
                $investor->blurred_email = $this->blurEmail($investor->email);
                return $investor;
            })
            ->sortByDesc(function($investor) {
                // Sort by likelihood first (highest first), then by total invested amount, then by investment date
                return [
                    $investor->likelihood_to_invest ?? 0,
                    $investor->total_invested,
                    $investor->investment_date ? $investor->investment_date->timestamp : 0
                ];
            })
            ->take(10);
            
        // Get investment statistics
        $stats = [
            'total_investors' => Investor::whereIn('status', ['active', 'pending'])->count(),
            'total_invested' => InvestorInvestment::whereIn('status', ['active', 'pending'])->sum('investment_amount'),
            'total_payouts' => \App\Models\InvestorPayout::sum('amount'),
            'average_roi' => $this->calculateAverageROI(),
        ];
            
        return view('public.investment.index', compact('branches', 'topInvestors', 'stats'));
    }
    
    /**
     * Show the investment leaderboard
     */
    public function leaderboard()
    {
        // Get top investors by total investment amount
        $topInvestors = Investor::with(['investments' => function($query) {
                $query->where('status', 'active');
            }])
            ->where('status', 'active')
            ->where('is_verified', true)
            ->get()
            ->map(function($investor) {
                $investor->total_invested = $investor->investments->sum('investment_amount');
                $investor->total_payouts = $investor->payouts->sum('amount');
                $investor->roi = $investor->total_invested > 0 ? 
                    (($investor->total_payouts - $investor->total_invested) / $investor->total_invested) * 100 : 0;
                $investor->blurred_email = $this->blurEmail($investor->email);
                return $investor;
            })
            ->sortByDesc('total_invested')
            ->take(50);
            
        // Get recent investments
        $recentInvestments = InvestorInvestment::with(['investor', 'branch'])
            ->where('status', 'active')
            ->orderBy('investment_date', 'desc')
            ->take(20)
            ->get();
            
        // Get investment statistics
        $stats = [
            'total_investors' => Investor::where('status', 'active')->count(),
            'total_invested' => InvestorInvestment::where('status', 'active')->sum('investment_amount'),
            'total_payouts' => \App\Models\InvestorPayout::sum('amount'),
            'average_roi' => $this->calculateAverageROI(),
        ];
        
        return view('public.investment.leaderboard', compact('topInvestors', 'recentInvestments', 'stats'));
    }
    
    /**
     * Handle investment registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:500',
            'investment_amount' => 'required|numeric|min:1000',
            'likelihood' => 'required|integer|between:1,5',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            // Create investor
            $investor = Investor::create([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'total_investment_amount' => $request->investment_amount,
                'investment_date' => now(),
                'status' => 'pending',
                'is_verified' => false,
                'likelihood_to_invest' => $request->likelihood,
            ]);
            
            // Create investment record
            $investment = InvestorInvestment::create([
                'investor_id' => $investor->id,
                'branch_id' => null,
                'investment_amount' => $request->investment_amount,
                'ownership_percentage' => 0,
                'investment_date' => now(),
                'status' => 'pending',
            ]);
            
            Log::info('New investment registration with likelihood', [
                'investor_id' => $investor->id,
                'investment_id' => $investment->id,
                'amount' => $request->investment_amount,
                'email' => $request->email,
                'likelihood' => $request->likelihood
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Investment registration submitted successfully! We will review your application and contact you soon.',
                'investor_id' => $investor->id,
                'investment_id' => $investment->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Investment registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit investment registration. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Calculate ownership percentage based on investment amount and branch value
     */
    private function calculateOwnershipPercentage($investmentAmount, $branch)
    {
        // Simple calculation: investment amount / branch estimated value
        // You can make this more sophisticated based on your business logic
        $branchValue = $branch->estimated_value ?? 100000; // Default branch value
        $percentage = ($investmentAmount / $branchValue) * 100;
        
        // Cap at 25% maximum ownership per investor per branch
        return min($percentage, 25);
    }
    
    /**
     * Calculate expected return based on investment amount and branch
     */
    private function calculateExpectedReturn($investmentAmount, $branch)
    {
        // Simple calculation: 15-25% annual return based on investment amount
        $baseReturn = 15;
        if ($investmentAmount >= 50000) {
            $baseReturn = 20;
        }
        if ($investmentAmount >= 100000) {
            $baseReturn = 25;
        }
        
        return $baseReturn;
    }
    
    /**
     * Calculate average ROI across all investors
     */
    private function calculateAverageROI()
    {
        $investors = Investor::whereIn('status', ['active', 'pending'])->get();
        $totalROI = 0;
        $count = 0;
        
        foreach ($investors as $investor) {
            $totalInvested = $investor->investments->sum('investment_amount');
            $totalPayouts = $investor->payouts->sum('amount');
            
            if ($totalInvested > 0) {
                $roi = (($totalPayouts - $totalInvested) / $totalInvested) * 100;
                $totalROI += $roi;
                $count++;
            }
        }
        
        return $count > 0 ? round($totalROI / $count, 2) : 0;
    }

    private function blurEmail($email)
    {
        if (!$email) {
            return 'N/A';
        }
        
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return 'N/A';
        }
        
        $username = $parts[0];
        $domain = $parts[1];
        
        $blurredUsername = substr($username, 0, 3) . '***';
        
        return $blurredUsername . '@' . $domain;
    }
} 