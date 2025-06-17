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
        $query = Supplier::withCount('items');

        if ($request->has('branch')) {
            $branch = Branch::findOrFail($request->branch);
            $query->whereHas('items', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            });
        }

        $suppliers = $query->orderBy('name')->paginate(10);
        $branches = Branch::orderBy('name')->get();

        return view('admin.suppliers.index', compact('suppliers', 'branches'));
    }

    public function create(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $selectedBranch = $request->has('branch') ? Branch::find($request->branch) : null;

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