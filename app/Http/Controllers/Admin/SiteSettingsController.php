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

        foreach ($request->settings as $key => $value) {
            $setting = SiteSetting::where('key', $key)->first();
            if ($setting) {
                $setting->update(['value' => $value]);
            }
        }

        // Clear cache if you're using caching
        Cache::forget('site_settings');

        return redirect()->back()->with('success', 'Site settings updated successfully!');
    }

    public function toggle($id)
    {
        $setting = SiteSetting::findOrFail($id);
        $setting->update(['is_active' => !$setting->is_active]);

        return redirect()->back()->with('success', 'Setting toggled successfully!');
    }
}
