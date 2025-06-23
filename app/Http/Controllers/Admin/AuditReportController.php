<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WeeklyStockCheck;
use App\Models\MonthlyStockCheck;
use App\Models\InventoryItem;
use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use PDF;
use Maatwebsite\Excel\Facades\Excel;

class AuditReportController extends Controller
{
    public function index(Request $request)
    {
        try {
            $branchId = $request->query('branch');
            $month = $request->query('month', now()->format('Y-m'));
            $type = $request->query('type', 'monthly'); // weekly or monthly

            // Get user's selected branch from session
            $selectedBranchId = session('selected_branch_id');
            
            // If no branch specified in request, use selected branch
            if (!$branchId) {
                $branchId = $selectedBranchId;
            }
            
            // If user is not from main branch, force them to only see their branch
            $user = auth()->user();
            $isMainBranch = $user && $user->branch && $user->branch->is_main;
            
            if (!$isMainBranch && $selectedBranchId) {
                $branchId = $selectedBranchId;
            }

            $branch = null;
            if ($branchId) {
                $branch = Branch::findOrFail($branchId);
            }

            // Get branches based on user access
            if ($isMainBranch) {
                $branches = Branch::orderBy('name')->get();
            } else {
                $branches = Branch::where('id', $selectedBranchId)->get();
            }

            // Get audit data
            $auditData = $this->getAuditSummary($branchId, $month, $type);
            
            // Get discrepancy trends
            $trends = $this->getDiscrepancyTrends($branchId, $type);
            
            // Get top discrepancies
            $topDiscrepancies = $this->getTopDiscrepancies($branchId, $month, $type);

            return view('admin.inventory.audit-reports.index', compact(
                'auditData', 
                'trends', 
                'topDiscrepancies', 
                'branch', 
                'branches', 
                'month', 
                'type'
            ));
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('AuditReportController index error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return a simple error view
            return view('admin.inventory.audit-reports.index', [
                'auditData' => [],
                'trends' => [],
                'topDiscrepancies' => collect(),
                'branch' => null,
                'branches' => collect(),
                'month' => now()->format('Y-m'),
                'type' => 'monthly',
                'error' => 'An error occurred while loading audit data. Please try again.'
            ]);
        }
    }

    public function getAuditSummary($branchId = null, $month = null, $type = 'monthly')
    {
        $query = $type === 'weekly' ? WeeklyStockCheck::query() : MonthlyStockCheck::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($month) {
            $startDate = Carbon::parse($month)->startOfMonth();
            $endDate = Carbon::parse($month)->endOfMonth();
            $query->whereBetween('checked_at', [$startDate, $endDate]);
        }

        $checks = $query->with(['inventoryItem', 'user', 'branch'])->get();

        $summary = [
            'total_items_checked' => $checks->count(),
            'total_discrepancies' => $checks->where('discrepancy_amount', '!=', 0)->count(),
            'total_discrepancy_value' => $checks->sum('discrepancy_value'),
            'damaged_items' => $checks->where('is_damaged', true)->count(),
            'missing_items' => $checks->where('is_missing', true)->count(),
            'overcounted_items' => $checks->where('discrepancy_amount', '>', 0)->count(),
            'undercounted_items' => $checks->where('discrepancy_amount', '<', 0)->count(),
            'matching_items' => $checks->where('discrepancy_amount', 0)->count(),
            'audit_sessions' => $checks->unique('audit_session_id')->count(),
            'total_value_checked' => $checks->sum(function($check) {
                return $check->quantity_checked * $check->inventoryItem->unit_price;
            })
        ];

        return $summary;
    }

    public function getDiscrepancyTrends($branchId = null, $type = 'monthly', $months = 6)
    {
        $trends = [];
        $model = $type === 'weekly' ? WeeklyStockCheck::class : MonthlyStockCheck::class;

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();

            $query = $model::query();
            
            if ($branchId) {
                $query->where('branch_id', $branchId);
            }

            $checks = $query->whereBetween('checked_at', [$startDate, $endDate])->get();

            $trends[] = [
                'month' => $date->format('M Y'),
                'total_items' => $checks->count(),
                'discrepancies' => $checks->where('discrepancy_amount', '!=', 0)->count(),
                'discrepancy_value' => $checks->sum('discrepancy_value'),
                'damaged_missing' => $checks->where('is_damaged', true)->count() + $checks->where('is_missing', true)->count()
            ];
        }

        return $trends;
    }

    public function getTopDiscrepancies($branchId = null, $month = null, $type = 'monthly', $limit = 10)
    {
        $query = $type === 'weekly' ? WeeklyStockCheck::query() : MonthlyStockCheck::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($month) {
            $startDate = Carbon::parse($month)->startOfMonth();
            $endDate = Carbon::parse($month)->endOfMonth();
            $query->whereBetween('checked_at', [$startDate, $endDate]);
        }

        return $query->with(['inventoryItem', 'branch'])
            ->where('discrepancy_amount', '!=', 0)
            ->orderByRaw('ABS(discrepancy_value) DESC')
            ->limit($limit)
            ->get();
    }

    public function exportPdf(Request $request)
    {
        $branchId = $request->query('branch');
        $month = $request->query('month', now()->format('Y-m'));
        $type = $request->query('type', 'monthly');

        // Get user's selected branch from session
        $selectedBranchId = session('selected_branch_id');
        
        // If no branch specified in request, use selected branch
        if (!$branchId) {
            $branchId = $selectedBranchId;
        }
        
        // If user is not from main branch, force them to only see their branch
        $user = auth()->user();
        $isMainBranch = $user && $user->branch && $user->branch->is_main;
        
        if (!$isMainBranch && $selectedBranchId) {
            $branchId = $selectedBranchId;
        }

        $branch = null;
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $auditData = $this->getAuditSummary($branchId, $month, $type);
        $trends = $this->getDiscrepancyTrends($branchId, $type);
        $topDiscrepancies = $this->getTopDiscrepancies($branchId, $month, $type);

        $pdf = PDF::loadView('admin.inventory.audit-reports.pdf', compact(
            'auditData', 
            'trends', 
            'topDiscrepancies', 
            'branch', 
            'month', 
            'type'
        ));

        $filename = "audit-report-{$type}-" . ($branch ? $branch->name : 'all-branches') . "-{$month}.pdf";
        
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $branchId = $request->query('branch');
        $month = $request->query('month', now()->format('Y-m'));
        $type = $request->query('type', 'monthly');

        // Get user's selected branch from session
        $selectedBranchId = session('selected_branch_id');
        
        // If no branch specified in request, use selected branch
        if (!$branchId) {
            $branchId = $selectedBranchId;
        }
        
        // If user is not from main branch, force them to only see their branch
        $user = auth()->user();
        $isMainBranch = $user && $user->branch && $user->branch->is_main;
        
        if (!$isMainBranch && $selectedBranchId) {
            $branchId = $selectedBranchId;
        }

        $branch = null;
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $auditData = $this->getAuditSummary($branchId, $month, $type);
        $trends = $this->getDiscrepancyTrends($branchId, $type);
        $topDiscrepancies = $this->getTopDiscrepancies($branchId, $month, $type);

        $filename = "audit-report-{$type}-" . ($branch ? $branch->name : 'all-branches') . "-{$month}.xlsx";

        return Excel::download(new \App\Exports\AuditReportExport($auditData, $trends, $topDiscrepancies, $branch, $month, $type), $filename);
    }

    public function detailedReport(Request $request)
    {
        $branchId = $request->query('branch');
        $month = $request->query('month', now()->format('Y-m'));
        $type = $request->query('type', 'monthly');
        $auditSessionId = $request->query('session');

        // Get user's selected branch from session
        $selectedBranchId = session('selected_branch_id');
        
        // If no branch specified in request, use selected branch
        if (!$branchId) {
            $branchId = $selectedBranchId;
        }
        
        // If user is not from main branch, force them to only see their branch
        $user = auth()->user();
        $isMainBranch = $user && $user->branch && $user->branch->is_main;
        
        if (!$isMainBranch && $selectedBranchId) {
            $branchId = $selectedBranchId;
        }

        $branch = null;
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $query = $type === 'weekly' ? WeeklyStockCheck::query() : MonthlyStockCheck::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($month) {
            $startDate = Carbon::parse($month)->startOfMonth();
            $endDate = Carbon::parse($month)->endOfMonth();
            $query->whereBetween('checked_at', [$startDate, $endDate]);
        }

        if ($auditSessionId) {
            $query->where('audit_session_id', $auditSessionId);
        }

        $checks = $query->with(['inventoryItem', 'user', 'branch'])
            ->orderBy('checked_at', 'desc')
            ->get();

        // Get branches based on user access
        if ($isMainBranch) {
            $branches = Branch::orderBy('name')->get();
        } else {
            $branches = Branch::where('id', $selectedBranchId)->get();
        }

        return view('admin.inventory.audit-reports.detailed', compact(
            'checks', 
            'branch', 
            'branches', 
            'month', 
            'type', 
            'auditSessionId'
        ));
    }

    public function auditSessions(Request $request)
    {
        $branchId = $request->query('branch');
        $type = $request->query('type', 'monthly');

        // Get user's selected branch from session
        $selectedBranchId = session('selected_branch_id');
        
        // If no branch specified in request, use selected branch
        if (!$branchId) {
            $branchId = $selectedBranchId;
        }
        
        // If user is not from main branch, force them to only see their branch
        $user = auth()->user();
        $isMainBranch = $user && $user->branch && $user->branch->is_main;
        
        if (!$isMainBranch && $selectedBranchId) {
            $branchId = $selectedBranchId;
        }

        $branch = null;
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $query = $type === 'weekly' ? WeeklyStockCheck::query() : MonthlyStockCheck::query();
        
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $sessions = $query->select('audit_session_id', 'checked_at', 'user_id', 'branch_id')
            ->selectRaw('COUNT(*) as items_checked')
            ->selectRaw('SUM(CASE WHEN discrepancy_amount != 0 THEN 1 ELSE 0 END) as discrepancies')
            ->selectRaw('SUM(discrepancy_value) as total_discrepancy_value')
            ->groupBy('audit_session_id', 'checked_at', 'user_id', 'branch_id')
            ->with(['user', 'branch'])
            ->orderBy('checked_at', 'desc')
            ->get();

        // Get branches based on user access
        if ($isMainBranch) {
            $branches = Branch::orderBy('name')->get();
        } else {
            $branches = Branch::where('id', $selectedBranchId)->get();
        }

        return view('admin.inventory.audit-reports.sessions', compact(
            'sessions', 
            'branch', 
            'branches', 
            'type'
        ));
    }

    public function test()
    {
        return view('admin.inventory.audit-reports.index', [
            'auditData' => [
                'total_items_checked' => 0,
                'total_discrepancies' => 0,
                'total_discrepancy_value' => 0,
                'damaged_items' => 0,
                'missing_items' => 0,
                'matching_items' => 0
            ],
            'trends' => [],
            'topDiscrepancies' => collect(),
            'branch' => null,
            'branches' => collect(),
            'month' => now()->format('Y-m'),
            'type' => 'monthly'
        ]);
    }
}
