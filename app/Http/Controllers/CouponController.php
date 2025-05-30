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
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'price' => 'required|numeric|min:0',
            'referral_code' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $coupon = Coupon::where('code', $request->code)
                          ->where('active', true)
                          ->first();

            if (!$coupon) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or inactive coupon code.'
                    ], 404);
                }
                return redirect()->back()
                    ->with('coupon_error', 'Invalid or inactive coupon code.')
                    ->withInput();
            }

            // Calculate discount
            $discount = $this->calculateDiscount($coupon, $request->price);
            
            // Handle referral if present
            if ($request->referral_code) {
                $creator = Creator::where('code', $request->referral_code)->first();
                if ($creator) {
                    $creator->referral_count = ($creator->referral_count ?? 0) + 1;
                    $creator->earnings = ($creator->earnings ?? 0) + 1;
                    $creator->points = ($creator->points ?? 0) + 10 + 1;
                    $creator->save();
                }
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Coupon applied successfully!',
                    'discount' => $discount,
                    'coupon' => $coupon
                ]);
            }

            return redirect()->back()
                ->with('coupon_success', "Coupon applied successfully! Discount: $" . number_format($discount, 2))
                ->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error applying coupon: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('coupon_error', 'Error applying coupon: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function calculateDiscount($coupon, $price)
    {
        if ($coupon->type === 'percentage') {
            return ($price * $coupon->value) / 100;
        }
        return $coupon->value;
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