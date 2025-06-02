<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\Referral;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CreatorDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $creator = $user->creator;

        if (!$creator) {
            return redirect()->route('creator.register');
        }

        $wallet = $user->wallet;

        $referrals = Referral::where('creator_id', auth()->user()->id)
            ->with('referredUser')
            ->latest()
            ->get();

        $stats = [
            'total_referrals' => Referral::where('creator_id', auth()->user()->id)->count(),
            'ordered_referrals' => Referral::where('creator_id', auth()->user()->id)->where('status', 'ordered')->count(),
            'referral_points' => auth()->user()->creator->points ?? 0
        ];

        // Get top 5 creators for leaderboard
        $topCreators = Creator::with('user')
            ->orderBy('points', 'desc')
            ->take(5)
            ->get();

        // Award additional discounts to top 5 creators
        $discounts = [50, 40, 30, 20, 10];
        foreach ($topCreators as $index => $creator) {
            if (isset($discounts[$index])) {
                $creator->additional_discount = $discounts[$index];
                $creator->save();
            }
        }

        return view('creator-dashboard.index', compact('referrals', 'stats', 'topCreators', 'wallet'));
    }

    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }

    public function home()
    {
        return redirect()->route('shop');
    }

    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $creator = auth()->user()->creator;
        
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($creator->avatar) {
                Storage::delete($creator->avatar);
            }
            
            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $creator->avatar = $path;
            $creator->save();
        }

        return redirect()->back()->with('success', 'Profile photo updated successfully!');
    }
} 