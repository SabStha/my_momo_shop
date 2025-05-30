<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreatorReward;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CreatorRewardController extends Controller
{
    public function index()
    {
        $creator = Auth::user()->creator;
        if (!$creator) {
            return view('creators.rewards')->with([
                'rewards' => [],
                'creator_error' => 'You do not have a creator profile. Please register as a creator to view rewards.'
            ]);
        }
        $rewards = CreatorReward::where('creator_id', $creator->id)->orderByDesc('month')->get();
        return view('creators.rewards', compact('rewards'));
    }

    public function claim($id)
    {
        $reward = CreatorReward::findOrFail($id);
        if ($reward->creator_id !== Auth::user()->creator->id || $reward->claimed) {
            abort(403);
        }
        $reward->claimed = true;
        $reward->claimed_at = Carbon::now();
        $reward->save();
        return redirect()->back()->with('success', 'Reward claimed!');
    }
} 