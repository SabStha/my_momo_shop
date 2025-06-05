<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Offer;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('account.index', compact('user'));
    }

    public function show()
    {
        $user = auth()->user();
        $wallet = $user->wallet;
        $transactions = $wallet ? $wallet->transactions()->latest()->take(5)->get() : collect();
        $orders = $user->orders()->latest()->get();
        $offers = Offer::active()->get();
        $settings = $user->settings ?? new UserSettings(['user_id' => $user->id]);

        return view('user.my-account', compact('user', 'wallet', 'transactions', 'orders', 'offers', 'settings'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'notifications.orders' => ['boolean'],
            'notifications.offers' => ['boolean'],
        ]);

        $settings = auth()->user()->settings ?? new UserSettings(['user_id' => auth()->id()]);
        $settings->notify_orders = $validated['notifications']['orders'] ?? false;
        $settings->notify_offers = $validated['notifications']['offers'] ?? false;
        $settings->save();

        return back()->with('success', 'Settings updated successfully.');
    }

    public function getReferralCode()
    {
        $user = auth()->user();
        
        if (!$user->referral_code) {
            $user->referral_code = strtoupper(substr(md5($user->id . time()), 0, 8));
            $user->save();
        }

        return response()->json(['code' => $user->referral_code]);
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => [
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

        $user = auth()->user();
        $file = $request->file('profile_picture');
        
        // Generate secure filename
        $extension = $file->getClientOriginalExtension();
        $filename = hash('sha256', time() . $user->id . $file->getClientOriginalName()) . '.' . $extension;
        
        // Delete old profile picture if exists
        if ($user->profile_picture) {
            Storage::delete('public/' . $user->profile_picture);
        }
        
        // Store new profile picture with secure filename
        $path = $file->storeAs('profile-pictures', $filename, 'public');
        $user->profile_picture = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile picture updated successfully.',
            'profile_picture' => $path
        ]);
    }
} 