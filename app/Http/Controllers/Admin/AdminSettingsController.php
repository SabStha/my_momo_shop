<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BulkSetting;
use App\Models\SiteSetting;

class AdminSettingsController extends Controller
{
    /**
     * Display admin settings
     */
    public function index()
    {
        $bulkDiscountPercentage = BulkSetting::getBulkDiscountPercentage();
        $codEnabled = SiteSetting::getValue('cod_enabled', '1') === '1';

        return view('admin.settings.index', compact('bulkDiscountPercentage', 'codEnabled'));
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

        SiteSetting::setValue(
            'cod_enabled',
            $request->boolean('cod_enabled') ? '1' : '0',
            'boolean',
            'payment',
            'Cash on Delivery',
            'Allow customers to pay with cash on delivery'
        );

        return redirect()->route('admin.settings')
            ->with('success', 'Settings updated successfully!');
    }
}
