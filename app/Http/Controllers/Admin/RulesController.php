<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rule;
use Illuminate\Http\Request;

class RulesController extends Controller
{
    public function index()
    {
        $rules = Rule::with('branch', 'creator')->orderBy('priority')->paginate(20);
        return view('admin.rules.index', compact('rules'));
    }

    public function create()
    {
        return view('admin.rules.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'conditions'  => 'nullable|array',
            'actions'     => 'nullable|array',
            'priority'    => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
            'branch_id'   => 'nullable|exists:branches,id',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active']  = $request->boolean('is_active', true);

        $rule = Rule::create($validated);

        return redirect()->route('admin.rules.index')
            ->with('success', "Rule \"{$rule->name}\" created.");
    }

    public function edit(Rule $rule)
    {
        return view('admin.rules.edit', compact('rule'));
    }

    public function update(Request $request, Rule $rule)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'conditions'  => 'nullable|array',
            'actions'     => 'nullable|array',
            'priority'    => 'nullable|integer|min:0',
            'is_active'   => 'boolean',
            'branch_id'   => 'nullable|exists:branches,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $rule->update($validated);

        return redirect()->route('admin.rules.index')
            ->with('success', "Rule \"{$rule->name}\" updated.");
    }

    public function destroy(Rule $rule)
    {
        $rule->delete();
        return redirect()->route('admin.rules.index')
            ->with('success', 'Rule deleted.');
    }
}
