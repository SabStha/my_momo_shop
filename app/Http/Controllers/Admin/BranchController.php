<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount([
            'products',
            'orders',
            'employees',
            'tables',
            'wallets'
        ])->get();

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches',
            'address' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'is_active' => 'boolean',
            'is_main' => 'boolean',
        ]);

        // Ensure only one main branch exists
        if ($validated['is_main']) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $branch = Branch::create($validated);

        return redirect()
            ->route('admin.branches.show', $branch)
            ->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $branch->load([
            'products' => fn($q) => $q->latest()->take(5),
            'orders' => fn($q) => $q->latest()->take(5),
            'employees' => fn($q) => $q->latest()->take(5),
            'tables',
            'wallets'
        ]);

        return view('admin.branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('branches')->ignore($branch->id)],
            'address' => 'required|string',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'is_active' => 'boolean',
            'is_main' => 'boolean',
        ]);

        // Ensure only one main branch exists
        if ($validated['is_main'] && !$branch->is_main) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $branch->update($validated);

        return redirect()
            ->route('admin.branches.show', $branch)
            ->with('success', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch)
    {
        // Prevent deletion of main branch
        if ($branch->is_main) {
            return back()->with('error', 'Cannot delete the main branch.');
        }

        // Check for associated data
        $hasData = DB::table('products')->where('branch_id', $branch->id)->exists() ||
            DB::table('orders')->where('branch_id', $branch->id)->exists() ||
            DB::table('employees')->where('branch_id', $branch->id)->exists() ||
            DB::table('tables')->where('branch_id', $branch->id)->exists() ||
            DB::table('wallets')->where('branch_id', $branch->id)->exists();

        if ($hasData) {
            return back()->with('error', 'Cannot delete branch with associated data.');
        }

        $branch->delete();

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Branch deleted successfully.');
    }
} 