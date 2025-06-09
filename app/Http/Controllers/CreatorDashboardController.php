<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\Referral;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class CreatorDashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasRole('admin')) {
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

            // Get all referrals for admin view
            $referrals = Referral::with('referredUser')->latest()->get();

            return view('admin.creator-dashboard.index', compact('creators', 'topCreators', 'stats', 'referrals'));
        }

        $user = auth()->user();
        $creator = $user->creator;

        if (!$creator) {
            return redirect()->route('home')->with('error', 'You are not registered as a creator.');
        }

        $referrals = Referral::where('creator_id', $creator->id)
            ->with('referredUser')
            ->latest()
            ->get();

        $stats = [
            'total_referrals' => $referrals->count(),
            'ordered_referrals' => $referrals->where('status', 'ordered')->count(),
            'referral_points' => $referrals->where('status', 'ordered')->sum('points')
        ];

        $wallet = $user->wallet;

        return view('admin.creator-dashboard.index', compact('creator', 'referrals', 'stats', 'wallet'));
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

        $user = auth()->user();
        $creator = $user->creator;
                    
        if (!$creator) {
            return redirect()->back()->with('error', 'You are not registered as a creator.');
        }
        
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($creator->avatar) {
                Storage::delete('public/avatars/' . $creator->avatar);
            }

            // Store new avatar
            $filename = time() . '_' . $request->file('avatar')->getClientOriginalName();
            $request->file('avatar')->storeAs('public/avatars', $filename);
            
            // Update creator avatar
            $creator->avatar = $filename;
            $creator->save();

            return redirect()->back()->with('success', 'Profile photo updated successfully');
        }

        return redirect()->back()->with('error', 'Failed to update profile photo');
    }
} 