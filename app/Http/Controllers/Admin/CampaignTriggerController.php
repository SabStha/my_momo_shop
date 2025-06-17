<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CampaignTrigger;
use App\Models\CustomerSegment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CampaignTriggerController extends Controller
{
    public function index()
    {
        $triggers = CampaignTrigger::with(['segment'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        $segments = CustomerSegment::all();

        return view('admin.campaigns.triggers.index', compact('triggers', 'segments'));
    }

    public function create()
    {
        $segments = CustomerSegment::all();
        return view('admin.campaigns.triggers.create', compact('segments'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|in:behavioral,scheduled,segment',
            'trigger_condition' => 'required|array',
            'campaign_type' => 'required|in:email,sms,push',
            'campaign_template' => 'required|string',
            'segment_id' => 'nullable|exists:customer_segments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'frequency' => 'required|in:once,daily,weekly,monthly',
            'cooldown_period' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            \Log::error('Campaign Trigger Validation Failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $trigger = CampaignTrigger::create($request->all());

        return redirect()
            ->route('admin.campaigns.triggers.index')
            ->with('success', 'Campaign trigger created successfully.');
    }

    public function edit(CampaignTrigger $trigger)
    {
        $segments = CustomerSegment::all();
        return view('admin.campaigns.triggers.edit', compact('trigger', 'segments'));
    }

    public function update(Request $request, CampaignTrigger $trigger)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|in:behavioral,scheduled,segment',
            'trigger_condition' => 'required|array',
            'campaign_type' => 'required|in:email,sms,push',
            'campaign_template' => 'required|string',
            'segment_id' => 'nullable|exists:customer_segments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'boolean',
            'frequency' => 'required|in:once,daily,weekly,monthly',
            'cooldown_period' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $trigger->update($request->all());

        return redirect()
            ->route('admin.campaigns.triggers.index')
            ->with('success', 'Campaign trigger updated successfully.');
    }

    public function destroy(CampaignTrigger $trigger)
    {
        $trigger->delete();

        return redirect()
            ->route('admin.campaigns.triggers.index')
            ->with('success', 'Campaign trigger deleted successfully.');
    }

    public function toggleStatus(CampaignTrigger $trigger)
    {
        $trigger->update(['is_active' => !$trigger->is_active]);

        return redirect()
            ->route('admin.campaigns.triggers.index')
            ->with('success', 'Campaign trigger status updated successfully.');
    }

    public function testTrigger(CampaignTrigger $trigger)
    {
        try {
            $users = $trigger->getUsersMatchingCondition();
            
            return response()->json([
                'success' => true,
                'message' => 'Trigger test completed successfully.',
                'data' => [
                    'matching_users_count' => $users->count(),
                    'sample_users' => $users->take(5)->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email
                        ];
                    })
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing trigger: ' . $e->getMessage()
            ], 500);
        }
    }

    public function recentActivity()
    {
        $triggers = CampaignTrigger::where('is_active', true)
            ->whereNotNull('last_triggered_at')
            ->orderBy('last_triggered_at', 'desc')
            ->take(5)
            ->get(['name', 'description', 'last_triggered_at']);

        return response()->json($triggers);
    }
} 