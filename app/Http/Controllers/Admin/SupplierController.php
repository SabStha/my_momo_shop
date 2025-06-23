<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('items')->with('branch');

        if ($request->has('branch')) {
            $branch = Branch::findOrFail($request->branch);
            
            // If the requested branch is the main branch, show its suppliers
            if ($branch->is_main) {
                $query->where('branch_id', $branch->id);
            } else {
                // If it's a regular branch, show suppliers from the main branch
                // since all suppliers are centralized there
                $mainBranch = Branch::where('is_main', true)->first();
                if ($mainBranch) {
                    $query->where('branch_id', $mainBranch->id);
                }
            }
        }

        $suppliers = $query->orderBy('name')->paginate(10);
        $branches = Branch::orderBy('name')->get();

        return view('admin.suppliers.index', compact('suppliers', 'branches'));
    }

    public function create(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $selectedBranch = null;
        
        if ($request->has('branch')) {
            $selectedBranch = Branch::find($request->branch);
            
            // If the selected branch is NOT the main branch, default to main branch for supplier creation
            if ($selectedBranch && !$selectedBranch->is_main) {
                $mainBranch = Branch::where('is_main', true)->first();
                if ($mainBranch) {
                    $selectedBranch = $mainBranch;
                }
            }
        } else {
            // If no branch is specified, default to main branch
            $selectedBranch = Branch::where('is_main', true)->first();
        }

        return view('admin.suppliers.create', compact('branches', 'selectedBranch'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        // Get the main branch
        $mainBranch = Branch::where('is_main', true)->first();
        
        // If no main branch exists, create one
        if (!$mainBranch) {
            $mainBranch = Branch::create([
                'name' => 'Main Branch',
                'code' => 'MB001',
                'address' => 'Main Branch Address',
                'contact_person' => 'Main Branch Contact',
                'email' => 'main@momoshop.com',
                'phone' => '1234567890',
                'is_active' => true,
                'is_main' => true
            ]);
        }

        // If a specific branch is provided, check if it's the main branch
        if ($request->filled('branch_id')) {
            $selectedBranch = Branch::find($request->branch_id);
            
            // If the selected branch is NOT the main branch, assign supplier to main branch
            if ($selectedBranch && !$selectedBranch->is_main) {
                $validated['branch_id'] = $mainBranch->id;
            }
            // If it's the main branch, keep the branch_id as is
        } else {
            // If no branch is specified, default to main branch
            $validated['branch_id'] = $mainBranch->id;
        }

        // Generate a unique code for the supplier
        $validated['code'] = Str::random(8);

        $supplier = Supplier::create($validated);

        return redirect()
            ->route('admin.suppliers.index', $request->has('branch_id') ? ['branch' => $request->branch_id] : [])
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['items' => function ($query) {
            $query->with('branch')->orderBy('name');
        }]);

        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        $branches = Branch::orderBy('name')->get();
        return view('admin.suppliers.edit', compact('supplier', 'branches'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'branch_id' => 'nullable|exists:branches,id'
        ]);

        // Get the main branch
        $mainBranch = Branch::where('is_main', true)->first();
        
        // If no main branch exists, create one
        if (!$mainBranch) {
            $mainBranch = Branch::create([
                'name' => 'Main Branch',
                'code' => 'MB001',
                'address' => 'Main Branch Address',
                'contact_person' => 'Main Branch Contact',
                'email' => 'main@momoshop.com',
                'phone' => '1234567890',
                'is_active' => true,
                'is_main' => true
            ]);
        }

        // If a specific branch is provided, check if it's the main branch
        if ($request->filled('branch_id')) {
            $selectedBranch = Branch::find($request->branch_id);
            
            // If the selected branch is NOT the main branch, assign supplier to main branch
            if ($selectedBranch && !$selectedBranch->is_main) {
                $validated['branch_id'] = $mainBranch->id;
            }
            // If it's the main branch, keep the branch_id as is
        } else {
            // If no branch is specified, default to main branch
            $validated['branch_id'] = $mainBranch->id;
        }

        $supplier->update($validated);

        return redirect()
            ->route('admin.suppliers.index', $request->has('branch_id') ? ['branch' => $request->branch_id] : [])
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->items()->exists()) {
            return redirect()
                ->route('admin.suppliers.index')
                ->with('error', 'Cannot delete supplier with associated items.');
        }

        $supplier->delete();

        return redirect()
            ->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
} 