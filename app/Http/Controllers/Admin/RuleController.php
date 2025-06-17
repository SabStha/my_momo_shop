<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rule;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RuleController extends Controller
{
    public function index()
    {
        $rules = Rule::where('branch_id', session('selected_branch_id'))
            ->orderBy('priority')
            ->get();

        return view('admin.rules.index', compact('rules'));
    }

    public function create()
    {
        $campaigns = Campaign::where('branch_id', session('selected_branch_id'))
            ->where('status', 'active')
            ->get();

        return view('admin.rules.create', compact('campaigns'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'conditions' => 'required|array',
            'actions' => 'required|array',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $rule = Rule::create([
                    'name' => $validated['name'],
                    'description' => $validated['description'],
                    'priority' => $validated['priority'],
                    'is_active' => $validated['is_active'],
                    'conditions' => $validated['conditions'],
                    'actions' => $validated['actions'],
                    'branch_id' => session('selected_branch_id'),
                    'created_by' => auth()->id()
                ]);
            });

            return redirect()->route('admin.rules.index')
                ->with('success', 'Rule created successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->withErrors(['error' => 'Error creating rule: ' . $e->getMessage()]);
        }
    }

    public function edit(Rule $rule)
    {
        $this->authorize('update', $rule);

        $campaigns = Campaign::where('branch_id', session('selected_branch_id'))
            ->where('status', 'active')
            ->get();

        return view('admin.rules.edit', compact('rule', 'campaigns'));
    }

    public function update(Request $request, Rule $rule)
    {
        $this->authorize('update', $rule);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'conditions' => 'required|array',
            'conditions.*.type' => 'required|string',
            'conditions.*.operator' => 'required_if:conditions.*.type,purchase_frequency,spending_amount,last_purchase|string',
            'conditions.*.value' => 'required|string',
            'conditions.*.period' => 'required_if:conditions.*.type,purchase_frequency,spending_amount|integer',
            'actions' => 'required|array',
            'actions.*.type' => 'required|string',
            'actions.*.campaign_id' => 'required_if:actions.*.type,launch_campaign|exists:campaigns,id',
            'actions.*.updates' => 'required_if:actions.*.type,update_customer|array',
            'actions.*.message' => 'required_if:actions.*.type,send_notification|string',
            'priority' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        DB::transaction(function () use ($rule, $validated) {
            $rule->update($validated);
            
            // Log the activity
            activity()
                ->performedOn($rule)
                ->causedBy(auth()->user())
                ->withProperties([
                    'branch_id' => $rule->branch_id,
                    'rule_name' => $validated['name']
                ])
                ->log('updated_rule');
        });

        return redirect()->route('admin.rules.index')
            ->with('success', 'Rule updated successfully.');
    }

    public function destroy(Rule $rule)
    {
        $this->authorize('delete', $rule);

        DB::transaction(function () use ($rule) {
            $ruleName = $rule->name;
            $branchId = $rule->branch_id;
            
            $rule->delete();
            
            // Log the activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'branch_id' => $branchId,
                    'rule_name' => $ruleName
                ])
                ->log('deleted_rule');
        });

        return redirect()->route('admin.rules.index')
            ->with('success', 'Rule deleted successfully.');
    }

    public function toggle(Rule $rule)
    {
        $this->authorize('update', $rule);

        $rule->update(['is_active' => !$rule->is_active]);

        activity()
            ->performedOn($rule)
            ->causedBy(auth()->user())
            ->withProperties([
                'branch_id' => $rule->branch_id,
                'rule_name' => $rule->name,
                'new_status' => $rule->is_active ? 'active' : 'inactive'
            ])
            ->log('toggled_rule');

        return redirect()->route('admin.rules.index')
            ->with('success', 'Rule status updated successfully.');
    }
} 