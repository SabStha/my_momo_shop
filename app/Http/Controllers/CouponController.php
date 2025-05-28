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
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $coupon = Coupon::where('code', $request->code)
                          ->where('active', true)
                          ->first();

            if (!$coupon) {
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

            return redirect()->back()
                ->with('coupon_success', "Coupon applied successfully! Discount: $" . number_format($discount, 2))
                ->withInput();
        } catch (\Exception $e) {
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
} 