<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch') ?? session('current_branch_id');
        $branch = null;
        
        if ($branchId) {
            $branch = Branch::findOrFail($branchId);
        }

        $query = Supplier::query();
        
        if ($branchId) {
            // When viewing a specific branch, show only its suppliers
            $query->whereHas('items', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $suppliers = $query->withCount('items')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.suppliers.index', compact('suppliers', 'branch'));
    }

    public function create(Request $request)
    {
        $branchId = $request->query('branch') ?? session('current_branch_id');
        
        if (!$branchId) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Please select a branch first.');
        }

        $branch = Branch::findOrFail($branchId);
        return view('admin.suppliers.create', compact('branch'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:suppliers',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        try {
            $supplier = Supplier::create($validated);

            $branchId = $request->query('branch') ?? session('current_branch_id');
            if ($branchId) {
                return redirect()->route('admin.suppliers.index', ['branch' => $branchId])
                    ->with('success', 'Supplier created successfully.');
            }

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating supplier: ' . $e->getMessage());
            return back()->with('error', 'Error creating supplier. Please try again.');
        }
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['items' => function($query) {
            $query->with('category')->orderBy('name');
        }]);

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:suppliers,email,' . $supplier->id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'contact_person' => 'nullable|string|max:255',
            'notes' => 'nullable|string'
        ]);

        try {
            $supplier->update($validated);

            $branchId = $request->query('branch') ?? session('current_branch_id');
            if ($branchId) {
                return redirect()->route('admin.suppliers.index', ['branch' => $branchId])
                    ->with('success', 'Supplier updated successfully.');
            }

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating supplier: ' . $e->getMessage());
            return back()->with('error', 'Error updating supplier. Please try again.');
        }
    }

    public function destroy(Supplier $supplier)
    {
        try {
            if ($supplier->items()->count() > 0) {
                return back()->with('error', 'Cannot delete supplier with associated items.');
            }

            $supplier->delete();

            $branchId = request()->query('branch') ?? session('current_branch_id');
            if ($branchId) {
                return redirect()->route('admin.suppliers.index', ['branch' => $branchId])
                    ->with('success', 'Supplier deleted successfully.');
            }

            return redirect()->route('admin.suppliers.index')
                ->with('success', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting supplier: ' . $e->getMessage());
            return back()->with('error', 'Error deleting supplier. Please try again.');
        }
    }
} 