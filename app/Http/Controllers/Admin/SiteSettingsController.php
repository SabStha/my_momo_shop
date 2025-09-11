<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteSettingsController extends Controller
{
    public function index()
    {
        $settings = SiteSetting::orderBy('group')->orderBy('label')->get()->groupBy('group');
        
        return view('admin.site-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable|string'
        ]);

        $updatedCount = 0;
        foreach ($request->settings as $key => $value) {
            $setting = SiteSetting::where('key', $key)->first();
            if ($setting) {
                // Only update if the value has actually changed
                if ($setting->value !== $value) {
                    $setting->update(['value' => $value]);
                    $updatedCount++;
                }
            }
        }

        // Clear cache if you're using caching
        Cache::forget('site_settings');

        if ($updatedCount > 0) {
            return redirect()->back()->with('success', "Site settings updated successfully! {$updatedCount} setting(s) changed.");
        } else {
            return redirect()->back()->with('info', 'No changes were made to the settings.');
        }
    }

    public function toggle($id)
    {
        $setting = SiteSetting::findOrFail($id);
        $setting->update(['is_active' => !$setting->is_active]);

        return redirect()->back()->with('success', 'Setting toggled successfully!');
    }
}
