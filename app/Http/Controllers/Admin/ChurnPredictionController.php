<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\ChurnPrediction;
use App\Services\ChurnPredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChurnPredictionController extends Controller
{
    protected $churnPredictionService;

    public function __construct(ChurnPredictionService $churnPredictionService)
    {
        $this->churnPredictionService = $churnPredictionService;
    }

    public function index()
    {
        $branch = Branch::find(session('selected_branch_id'));
        if (!$branch) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Please select a branch first.');
        }
        
        $highRiskCustomers = $this->churnPredictionService->getHighRiskCustomers($branch);
        
        return view('admin.churn.index', compact('highRiskCustomers'));
    }

    public function updatePredictions()
    {
        $branch = Branch::find(session('selected_branch_id'));
        if (!$branch) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Please select a branch first.');
        }
        
        DB::beginTransaction();
        try {
            $customers = Customer::whereHas('purchases', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })->get();
            
            if ($customers->isEmpty()) {
                return redirect()->back()->with('info', 'No customers with purchase history found for this branch.');
            }
            
            $updatedCount = 0;
            foreach ($customers as $customer) {
                try {
                    $this->churnPredictionService->calculateChurnProbability($customer, $branch);
                    $updatedCount++;
                } catch (\Exception $e) {
                    \Log::error("Failed to calculate churn probability for customer {$customer->id}: " . $e->getMessage());
                    continue;
                }
            }
            
            DB::commit();
            
            if ($updatedCount === 0) {
                return redirect()->back()->with('error', 'Failed to update any churn predictions. Please check the logs for details.');
            }
            
            return redirect()->back()->with('success', "Successfully updated churn predictions for {$updatedCount} customers.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Churn prediction update failed: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update churn predictions. Please check the logs for details.');
        }
    }

    public function show(Customer $customer)
    {
        $branch = Branch::find(session('selected_branch_id'));
        if (!$branch) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Please select a branch first.');
        }
        
        $prediction = ChurnPrediction::where('customer_id', $customer->id)
            ->where('branch_id', $branch->id)
            ->first();
            
        if (!$prediction) {
            $churnProbability = $this->churnPredictionService->calculateChurnProbability($customer, $branch);
            $prediction = ChurnPrediction::where('customer_id', $customer->id)
                ->where('branch_id', $branch->id)
                ->first();
        }
        
        return view('admin.churn.show', compact('customer', 'prediction'));
    }
} 