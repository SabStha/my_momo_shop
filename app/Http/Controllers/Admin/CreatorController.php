<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\Referral;
use App\Models\User;
use App\Models\CreatorEarning;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreatorController extends Controller
{
    public function index()
    {
        $creators = User::role('creator')->with('creator')->get();
        
        $topCreators = User::role('creator')
            ->with(['creator' => function($query) {
                $query->withCount(['referrals' => function($query) {
                    $query->where('status', 'ordered');
                }]);
            }])
            ->get()
            ->sortByDesc(function($user) {
                return $user->creator->referrals_count;
            })
            ->take(5);

        $stats = [
            'total_referrals' => Referral::count(),
            'ordered_referrals' => Referral::where('status', 'ordered')->count(),
            'referral_points' => Referral::where('status', 'ordered')->sum('points')
        ];

        $referrals = Referral::with(['creator', 'referredUser'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.creators.index', compact('creators', 'topCreators', 'stats', 'referrals'));
    }

    public function show(Creator $creator)
    {
        $creator->load(['user', 'referrals.referredUser', 'earnings', 'rewards']);
        
        $stats = [
            'total_referrals' => $creator->referrals()->count(),
            'successful_referrals' => $creator->referrals()->where('status', 'ordered')->count(),
            'total_earnings' => $creator->earnings()->sum('amount'),
            'pending_earnings' => $creator->earnings()->where('status', 'pending')->sum('amount'),
            'total_points' => $creator->points,
            'rank' => $creator->rank
        ];

        return view('admin.creators.show', compact('creator', 'stats'));
    }

    public function edit(Creator $creator)
    {
        return view('admin.creators.edit', compact('creator'));
    }

    public function update(Request $request, Creator $creator)
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:0',
            'bio' => 'nullable|string|max:500',
            'status' => 'required|in:active,suspended'
        ]);

        $creator->update($validated);

        return redirect()
            ->route('admin.creators.show', $creator)
            ->with('success', 'Creator updated successfully');
    }

    public function referrals()
    {
        $referrals = Referral::with(['creator', 'referredUser'])
            ->latest()
            ->paginate(20);

        return view('admin.creators.referrals', compact('referrals'));
    }

    public function payouts()
    {
        $payouts = PayoutRequest::with('creator')
            ->latest()
            ->paginate(20);

        return view('admin.creators.payouts', compact('payouts'));
    }

    public function processPayout(Request $request, PayoutRequest $payout)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function() use ($payout, $validated) {
            $payout->update([
                'status' => $validated['status'],
                'processed_at' => now(),
                'notes' => $validated['notes']
            ]);

            if ($validated['status'] === 'approved') {
                // Create earning record for the payout
                CreatorEarning::create([
                    'creator_id' => $payout->creator_id,
                    'amount' => $payout->amount,
                    'type' => 'payout',
                    'description' => 'Payout processed',
                    'status' => 'completed'
                ]);
            }
        });

        return redirect()
            ->route('admin.creators.payouts')
            ->with('success', 'Payout request processed successfully');
    }

    public function rewards()
    {
        $rewards = CreatorReward::with('creator')
            ->latest()
            ->paginate(20);

        return view('admin.creators.rewards', compact('rewards'));
    }

    public function assignReward(Request $request, Creator $creator)
    {
        $validated = $request->validate([
            'badge' => 'required|string',
            'reward' => 'required|string',
            'month' => 'required|date'
        ]);

        $creator->rewards()->create($validated);

        return redirect()
            ->route('admin.creators.show', $creator)
            ->with('success', 'Reward assigned successfully');
    }
} 