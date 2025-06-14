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
            'referral_points' => $creator->points
        ];

        $wallet = $user->wallet;

        // Get top creators for the leaderboard
        $topCreators = User::role('creator')
            ->with(['creator' => function($query) {
                $query->withCount(['referrals' => function($query) {
                    $query->where('status', 'ordered');
                }]);
            }])
            ->with('creator.user')
            ->get()
            ->sortByDesc(function($user) {
                return $user->creator->referrals_count;
            })
            ->take(5);

        return view('creator.dashboard', compact('creator', 'referrals', 'stats', 'wallet', 'topCreators'));
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
            try {
                // Delete old avatar if exists
                if ($creator->avatar) {
                    Storage::disk('public')->delete($creator->avatar);
                }

                // Store new avatar
                $file = $request->file('avatar');
                $filename = time() . '_' . $file->getClientOriginalName();
                
                // Store the file in the public disk under avatars directory
                $path = $file->storeAs('avatars', $filename, 'public');
                
                if (!$path) {
                    throw new \Exception('Failed to store the file');
                }
                
                // Update creator avatar with the correct path
                $creator->avatar = $path;
                $creator->save();

                return redirect()->back()->with('success', 'Profile photo updated successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to update profile photo: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to update profile photo: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('error', 'No file was uploaded');
    }
} 