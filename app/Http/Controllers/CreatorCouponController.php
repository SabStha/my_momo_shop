<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Coupon;

class CreatorCouponController extends Controller
{
    public function generate(Request $request)
    {
        $creator = Auth::user()->creator;
        if (!$creator) {
            return redirect()->route('creator-dashboard.index')->with('error', 'You must be a creator to generate a coupon.');
        }
        // Check if creator already has a coupon
        $existing = Coupon::where('creator_id', $creator->id)->first();
        if ($existing) {
            return view('creators.coupons.generate', ['coupon' => $existing]);
        }
        if ($request->isMethod('post')) {
            // Predefined rules: code, type, value, active, etc.
            $coupon = new Coupon();
            $coupon->code = strtoupper($creator->code . rand(100,999));
            $coupon->type = 'percent';
            $coupon->value = 10;
            $coupon->active = true;
            $coupon->creator_id = $creator->id;
            $coupon->campaign_name = $creator->code;
            $coupon->save();
            return view('creators.coupons.generate', ['coupon' => $coupon]);
        }
        return view('creators.coupons.generate');
    }
} 