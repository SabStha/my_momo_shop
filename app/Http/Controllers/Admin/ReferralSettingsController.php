<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class ReferralSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::whereIn('key', [
            'referral_welcome_bonus',
            'referral_first_order_bonus',
            'referral_subsequent_order_bonus',
            'creator_referral_bonus',
            'creator_first_order_bonus',
            'creator_subsequent_order_bonus',
            'max_referral_orders'
        ])->pluck('value', 'key')->toArray();

        return view('admin.referral-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'referral_welcome_bonus' => 'required|numeric|min:0',
            'referral_first_order_bonus' => 'required|numeric|min:0',
            'referral_subsequent_order_bonus' => 'required|numeric|min:0',
            'creator_referral_bonus' => 'required|numeric|min:0',
            'creator_first_order_bonus' => 'required|numeric|min:0',
            'creator_subsequent_order_bonus' => 'required|numeric|min:0',
            'max_referral_orders' => 'required|numeric|min:1|max:100'
        ]);

        foreach ($validated as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value]);
        }

        // Clear the settings cache
        Cache::forget('settings');

        return redirect()->route('admin.referral-settings.index')
            ->with('success', 'Referral settings updated successfully.');
    }
} 