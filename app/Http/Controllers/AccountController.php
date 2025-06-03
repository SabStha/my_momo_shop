<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Offer;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
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
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = auth()->user();
        $file = $request->file('profile_picture');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->storeAs('profile-pictures', $filename, 'public');

        $user->profile_picture = 'profile-pictures/' . $filename;
        $user->save();

        return redirect()->route('my-account')->with('success', 'Profile picture updated successfully.');
    }
} 