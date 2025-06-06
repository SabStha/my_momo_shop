<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use App\Services\CouponService;
use App\Models\Creator;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string'
        ]);

        $coupon = Coupon::where('code', $request->coupon_code)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired coupon code'
            ]);
        }

        // Calculate discount amount
        $subtotal = Session::get('cart_subtotal', 0);
        $discountAmount = $coupon->type === 'percentage' 
            ? ($subtotal * $coupon->value / 100)
            : $coupon->value;

        // Store coupon info in session
        Session::put('coupon', [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value
        ]);
        Session::put('discount_amount', $discountAmount);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully',
            'discount' => $discountAmount
        ]);
    }

    public function remove()
    {
        Session::forget(['coupon', 'discount_amount']);
        
        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully'
        ]);
    }

    public function create()
    {
        $creators = \App\Models\Creator::with('user')->get();
        return view('admin.coupons.create', compact('creators'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'active' => 'required|boolean',
            'creator_id' => 'nullable|exists:creators,id',
            'campaign_name' => 'nullable|string',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);
        $coupon = new \App\Models\Coupon($validated);
        $coupon->save();
        return redirect()->route('admin.coupons.create')->with('success', 'Coupon created successfully!');
    }
} 