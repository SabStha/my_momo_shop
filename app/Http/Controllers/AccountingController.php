<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|investor');
    }

    public function dashboard()
    {
        $user = Auth::user();
        
        // Get date range (default to last 12 months)
        $endDate = now();
        $startDate = now()->subMonths(12);
        
        // Get all expenses in date range
        $expenses = Expense::byDateRange($startDate, $endDate)
            ->orderBy('date', 'desc')
            ->get();

        // Calculate summary metrics
        $totalExpenses = $expenses->sum('amount');
        $expenseCount = $expenses->count();
        
        // Category breakdown
        $categoryBreakdown = $expenses->groupBy('category')->map(function ($categoryExpenses) {
            return [
                'total' => $categoryExpenses->sum('amount'),
                'count' => $categoryExpenses->count(),
                'percentage' => 0 // Will be calculated below
            ];
        });

        // Calculate percentages
        if ($totalExpenses > 0) {
            $categoryBreakdown = $categoryBreakdown->map(function ($data) use ($totalExpenses) {
                $data['percentage'] = round(($data['total'] / $totalExpenses) * 100, 1);
                return $data;
            });
        }

        // Payment method breakdown
        $paymentMethodBreakdown = $expenses->groupBy('payment_method')->map(function ($methodExpenses) use ($totalExpenses) {
            return [
                'total' => $methodExpenses->sum('amount'),
                'count' => $methodExpenses->count(),
                'percentage' => $totalExpenses > 0 ? round(($methodExpenses->sum('amount') / $totalExpenses) * 100, 1) : 0
            ];
        });

        // Paid by breakdown
        $paidByBreakdown = $expenses->groupBy('paid_by')->map(function ($personExpenses) use ($totalExpenses) {
            return [
                'total' => $personExpenses->sum('amount'),
                'count' => $personExpenses->count(),
                'percentage' => $totalExpenses > 0 ? round(($personExpenses->sum('amount') / $totalExpenses) * 100, 1) : 0
            ];
        });

        // Monthly trends
        $monthlyTrends = $expenses->groupBy(function ($expense) {
            return $expense->date->format('Y-m');
        })->map(function ($monthExpenses, $month) {
            return [
                'month' => $month,
                'total' => $monthExpenses->sum('amount'),
                'count' => $monthExpenses->count()
            ];
        })->sortBy('month');

        // Recent expenses (last 10)
        $recentExpenses = $expenses->take(10);

        // Top expenses by amount
        $topExpenses = $expenses->sortByDesc('amount')->take(5);

        // Status breakdown
        $statusBreakdown = $expenses->groupBy('status')->map(function ($statusExpenses) {
            return [
                'count' => $statusExpenses->count(),
                'total' => $statusExpenses->sum('amount')
            ];
        });

        return view('accounting.dashboard', compact(
            'expenses',
            'totalExpenses',
            'expenseCount',
            'categoryBreakdown',
            'paymentMethodBreakdown',
            'paidByBreakdown',
            'monthlyTrends',
            'recentExpenses',
            'topExpenses',
            'statusBreakdown'
        ));
    }

    public function spreadsheet(Request $request)
    {
        $query = Expense::query();

        // Apply filters (same as index method)
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('payment_method')) {
            $query->byPaymentMethod($request->payment_method);
        }

        if ($request->filled('paid_by')) {
            $query->byPaidBy($request->paid_by);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('notes', 'like', '%' . $request->search . '%')
                  ->orWhere('paid_by', 'like', '%' . $request->search . '%');
            });
        }

        $expenses = $query->orderBy('date', 'desc')->paginate(50); // More items for spreadsheet view

        // Calculate totals for filtered data
        $totalExpenses = $query->sum('amount');
        $expenseCount = $query->count();

        // Category breakdown for filtered data
        $categoryBreakdown = $query->get()->groupBy('category')->map(function ($categoryExpenses) use ($totalExpenses) {
            return [
                'total' => $categoryExpenses->sum('amount'),
                'count' => $categoryExpenses->count(),
                'percentage' => $totalExpenses > 0 ? round(($categoryExpenses->sum('amount') / $totalExpenses) * 100, 1) : 0
            ];
        });

        // Payment method breakdown for filtered data
        $paymentMethodBreakdown = $query->get()->groupBy('payment_method')->map(function ($methodExpenses) use ($totalExpenses) {
            return [
                'total' => $methodExpenses->sum('amount'),
                'count' => $methodExpenses->count(),
                'percentage' => $totalExpenses > 0 ? round(($methodExpenses->sum('amount') / $totalExpenses) * 100, 1) : 0
            ];
        });

        // Get filter options
        $categories = Expense::getCategories();
        $paymentMethods = Expense::getPaymentMethods();
        $paidByOptions = Expense::distinct()->pluck('paid_by')->filter()->sort()->values();

        return view('accounting.spreadsheet', compact(
            'expenses',
            'totalExpenses',
            'expenseCount',
            'categoryBreakdown',
            'paymentMethodBreakdown',
            'categories',
            'paymentMethods',
            'paidByOptions'
        ));
    }

    public function index(Request $request)
    {
        $query = Expense::query();

        // Apply filters
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('payment_method')) {
            $query->byPaymentMethod($request->payment_method);
        }

        if ($request->filled('paid_by')) {
            $query->byPaidBy($request->paid_by);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                  ->orWhere('notes', 'like', '%' . $request->search . '%')
                  ->orWhere('paid_by', 'like', '%' . $request->search . '%');
            });
        }

        $expenses = $query->orderBy('date', 'desc')->paginate(20);

        // Get filter options
        $categories = Expense::getCategories();
        $paymentMethods = Expense::getPaymentMethods();
        $paidByOptions = Expense::distinct()->pluck('paid_by')->filter()->sort()->values();
        $statusOptions = ['pending', 'approved', 'rejected'];

        return view('accounting.index', compact(
            'expenses',
            'categories',
            'paymentMethods',
            'paidByOptions',
            'statusOptions'
        ));
    }

    public function create()
    {
        $categories = Expense::getCategories();
        $paymentMethods = Expense::getPaymentMethods();
        $transactionTypes = Expense::getTransactionTypes();

        return view('accounting.create', compact('categories', 'paymentMethods', 'transactionTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'transaction_type' => 'required|in:Expense,Income',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'paid_by' => 'nullable|string|max:255',
            'received_from' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        Expense::create([
            'date' => $request->date,
            'transaction_type' => $request->transaction_type,
            'category' => $request->category,
            'description' => $request->description,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'paid_by' => $request->paid_by,
            'received_from' => $request->received_from,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
            'status' => 'pending',
            'created_by' => Auth::id()
        ]);

        return redirect()->route('accounting.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function show(Expense $expense)
    {
        return view('accounting.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $categories = Expense::getCategories();
        $paymentMethods = Expense::getPaymentMethods();
        $transactionTypes = Expense::getTransactionTypes();

        return view('accounting.edit', compact('expense', 'categories', 'paymentMethods', 'transactionTypes'));
    }

    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'date' => 'required|date',
            'transaction_type' => 'required|in:Expense,Income',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'paid_by' => 'nullable|string|max:255',
            'received_from' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        $expense->update([
            'date' => $request->date,
            'transaction_type' => $request->transaction_type,
            'category' => $request->category,
            'description' => $request->description,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'paid_by' => $request->paid_by,
            'received_from' => $request->received_from,
            'reference_number' => $request->reference_number,
            'notes' => $request->notes,
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('accounting.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('accounting.index')
            ->with('success', 'Expense deleted successfully.');
    }

    public function approve(Expense $expense)
    {
        $expense->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Expense approved successfully.');
    }

    public function reject(Expense $expense)
    {
        $expense->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Expense rejected successfully.');
    }

}
