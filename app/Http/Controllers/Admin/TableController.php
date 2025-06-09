<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $query = Table::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $tables = $query->with('branch')->get();

        return view('admin.tables.index', compact('tables'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.tables.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|in:available,occupied,reserved',
            'is_active' => 'boolean',
        ]);

        $table = Table::create($validated);

        return redirect()
            ->route('admin.tables.index', ['branch' => $table->branch_id])
            ->with('success', 'Table created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Table $table)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('admin.tables.edit', compact('table', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'number' => ['required', 'string', 'max:50', Rule::unique('tables')->ignore($table->id)],
            'capacity' => 'required|integer|min:1',
            'branch_id' => 'required|exists:branches,id',
            'status' => 'required|in:available,occupied,reserved',
            'is_active' => 'boolean',
        ]);

        $table->update($validated);

        return redirect()
            ->route('admin.tables.index', ['branch' => $table->branch_id])
            ->with('success', 'Table updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Table $table)
    {
        // Check if table has any active orders
        if ($table->orders()->where('status', '!=', 'completed')->exists()) {
            return back()->with('error', 'Cannot delete table with active orders.');
        }

        $table->delete();

        return redirect()
            ->route('admin.tables.index', ['branch' => $table->branch_id])
            ->with('success', 'Table deleted successfully.');
    }
}
