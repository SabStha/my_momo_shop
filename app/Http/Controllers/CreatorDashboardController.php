<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CreatorDashboardController extends Controller
{
    public function index()
    {
        $creator = Creator::where('user_id', auth()->id())->firstOrFail();
        $referrals = Referral::where('referrer_id', $creator->id)->with('referredUser')->get();
        return view('creator-dashboard.index', compact('creator', 'referrals'));
    }

    public function generateReferral()
    {
        $creator = Creator::where('user_id', auth()->id())->firstOrFail();
        $couponCode = Str::random(10);
        $referral = Referral::create([
            'referrer_id' => $creator->id,
            'coupon_code' => $couponCode,
            'status' => 'pending',
        ]);
        return response()->json(['coupon_code' => $couponCode]);
    }
} 