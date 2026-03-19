<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BulkSetting;
use App\Models\Setting;
use App\Models\SiteSetting;

class AdminSettingsController extends Controller
{
    /**
     * Display admin settings
     */
    public function index()
    {
        $bulkDiscountPercentage  = BulkSetting::getBulkDiscountPercentage();
        $codEnabled              = SiteSetting::getValue('cod_enabled', '1') === '1';
        $cashDrawerPassword      = Setting::getValue('cash_drawer_password', '1234');

        return view('admin.settings.index', compact('bulkDiscountPercentage', 'codEnabled', 'cashDrawerPassword'));
    }

    /**
     * Update admin settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'bulk_discount_percentage'   => 'required|numeric|min:0|max:100',
            'cash_drawer_password'       => 'nullable|string|min:4',
            'cash_drawer_password_confirm' => 'nullable|same:cash_drawer_password',
        ], [
            'cash_drawer_password.min'              => 'Password must be at least 4 characters.',
            'cash_drawer_password_confirm.same'     => 'Passwords do not match.',
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

        if ($request->filled('cash_drawer_password')) {
            Setting::setValue('cash_drawer_password', $request->cash_drawer_password);
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Settings updated successfully!');
    }
}
