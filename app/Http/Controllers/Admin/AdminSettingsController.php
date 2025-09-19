<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BulkSetting;

class AdminSettingsController extends Controller
{
    /**
     * Display admin settings
     */
    public function index()
    {
        $bulkDiscountPercentage = BulkSetting::getBulkDiscountPercentage();
        
        return view('admin.settings.index', compact('bulkDiscountPercentage'));
    }

    /**
     * Update admin settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'bulk_discount_percentage' => 'required|numeric|min:0|max:100'
        ]);

        BulkSetting::setBulkDiscountPercentage($request->bulk_discount_percentage);

        return redirect()->route('admin.settings')
            ->with('success', 'Settings updated successfully!');
    }
} 