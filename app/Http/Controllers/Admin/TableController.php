<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $branches = Branch::orderBy('name')->get();
        $tables   = Table::with('branch')
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get()
            ->groupBy('branch_id');

        return view('admin.tables.index', compact('branches', 'tables'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        return view('admin.tables.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:191',
            'number'    => 'required|string|max:191|unique:tables,number',
            'capacity'  => 'required|integer|min:1|max:99',
        ]);

        Table::create(array_merge($validated, [
            'is_active'   => true,
            'is_occupied' => false,
            'status'      => 'available',
        ]));

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table created successfully.');
    }

    public function edit(Table $table)
    {
        $branches = Branch::orderBy('name')->get();
        return view('admin.tables.edit', compact('table', 'branches'));
    }

    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name'      => 'required|string|max:191',
            'number'    => 'required|string|max:191|unique:tables,number,' . $table->id,
            'capacity'  => 'required|integer|min:1|max:99',
        ]);

        $table->update($validated);

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table updated successfully.');
    }

    public function destroy(Table $table)
    {
        $table->delete();

        return redirect()->route('admin.tables.index')
            ->with('success', 'Table deleted.');
    }

    public function toggle(Table $table)
    {
        $table->update(['is_active' => !$table->is_active]);
        $state = $table->fresh()->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Table \"{$table->name}\" {$state}.");
    }
}
