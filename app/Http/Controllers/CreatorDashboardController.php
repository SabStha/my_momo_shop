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
        // If user is admin, show admin dashboard
        if (auth()->user()->hasRole('admin')) {
            $creators = Creator::with(['user', 'referrals.referredUser'])->get();
            $topCreators = Creator::with('user')
                ->orderBy('referral_count', 'desc')
                ->take(10)
                ->get();

            return view('admin.creator-dashboard.index', compact('creators', 'topCreators'));
        }

        // For regular creators
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
            'avatar' => [
                'required',
                'file',
                'mimes:jpeg,png,jpg',
                'max:2048',
                'dimensions:max_width=2000,max_height=2000',
                function ($attribute, $value, $fail) {
                    // Validate file content by MIME type
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $value->getPathname());
                    finfo_close($finfo);
                    
                    if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                        $fail('Invalid file type detected.');
                    }
                    
                    // Check file size again to prevent bypass
                    if ($value->getSize() > 2097152) { // 2MB in bytes
                        $fail('File size exceeds maximum allowed.');
                    }
                }
            ]
        ]);

        $creator = auth()->user()->creator;
        
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            
            // Generate secure filename
            $extension = $file->getClientOriginalExtension();
            $filename = hash('sha256', time() . auth()->id() . $file->getClientOriginalName()) . '.' . $extension;
            
            // Delete old avatar if exists
            if ($creator->avatar) {
                Storage::delete($creator->avatar);
            }
            
            // Store new avatar with secure filename
            $path = $file->storeAs('avatars', $filename, 'public');
            $creator->avatar = $path;
            $creator->save();
        }

        return redirect()->back()->with('success', 'Profile photo updated successfully!');
    }
} 