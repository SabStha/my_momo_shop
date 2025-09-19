<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\BulkSetting;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteSettingsController extends Controller
{
    public function index()
    {
        // Get settings from site_settings table
        $settings = SiteSetting::orderBy('group')->orderBy('label')->get()->groupBy('group');
        
        // Get bulk discount percentage
        $bulkDiscountPercentage = BulkSetting::getBulkDiscountPercentage();
        
        return view('admin.site-settings.index', compact('settings', 'bulkDiscountPercentage'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'nullable|array',
            'settings.*' => 'nullable|string',
            'bulk_discount_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $updatedCount = 0;
        
        // Update site settings
        if ($request->has('settings')) {
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
        }
        
        // Update bulk discount percentage
        if ($request->has('bulk_discount_percentage')) {
            BulkSetting::setBulkDiscountPercentage($request->bulk_discount_percentage);
            $updatedCount++;
        }
        

        // Clear cache if you're using caching
        Cache::forget('site_settings');
        Cache::forget('homepage_statistics'); // Clear statistics cache when settings change
        
        // Also clear statistics service cache
        $statisticsService = new StatisticsService();
        $statisticsService->clearCache();

        if ($updatedCount > 0) {
            return redirect()->back()->with('success', "Settings updated successfully! {$updatedCount} item(s) changed.");
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
