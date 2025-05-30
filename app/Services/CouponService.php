<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use App\Models\UserCoupon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Creator;

class CouponService
{
    /**
     * Validate and redeem a coupon for a user.
     *
     * @param User $user
     * @param string $code
     * @param array $context (e.g., ['is_shop' => true/false])
     * @return array ['success' => bool, 'message' => string, 'discount' => int|null, 'coupon' => Coupon|null]
     */
    public function validateAndRedeem(User $user, string $code, array $context = [])
    {
        try {
            $coupon = Coupon::where('code', $code)->first();
            if (!$coupon) {
                $this->logAttempt($user, $code, false, 'Coupon not found');
                return ['success' => false, 'message' => 'Coupon not found'];
            }

            // Validate coupon
            if (!$this->isValid($coupon)) {
                $this->logAttempt($user, $code, false, 'Invalid or expired coupon code');
                return ['success' => false, 'message' => 'Invalid or expired coupon code'];
            }

            // Check if user has already used this coupon
            if ($this->hasUserUsedCoupon($user, $coupon)) {
                $this->logAttempt($user, $code, false, 'You have already used this coupon');
                return ['success' => false, 'message' => 'You have already used this coupon'];
            }

            // Check if coupon usage limit is reached
            if ($this->isUsageLimitReached($coupon)) {
                $this->logAttempt($user, $code, false, 'Coupon usage limit reached');
                return ['success' => false, 'message' => 'Coupon usage limit reached'];
            }

            // Redeem coupon
            $redeemed = $this->redeemCoupon($user, $coupon, $context);
            if (!$redeemed) {
                $this->logAttempt($user, $code, false, 'Failed to redeem coupon');
                return ['success' => false, 'message' => 'Failed to redeem coupon'];
            }

            // --- Creator referral/earnings logic ---
            // If coupon has a campaign_name that matches a creator code, increment referral_count and earnings
            if ($coupon->campaign_name) {
                $creator = \App\Models\Creator::where('code', $coupon->campaign_name)->first();
                if ($creator) {
                    $creator->referral_count = ($creator->referral_count ?? 0) + 1;
                    $creator->earnings = ($creator->earnings ?? 0) + 1; // $1 per redemption, adjust as needed
                    $creator->points = ($creator->points ?? 0) + 10 + 1; // 10 per referral, 1 per $1 earned
                    $creator->save();
                }
            }
            // --- End creator logic ---

            $discount = $coupon->amount;
            $discountType = $coupon->type;
            $this->logAttempt($user, $code, true, 'Coupon redeemed');

            return [
                'success' => true,
                'message' => 'Coupon applied successfully.',
                'discount' => $discount,
                'discount_type' => $discountType,
                'coupon' => $coupon,
            ];

        } catch (\Exception $e) {
            \Log::error('Coupon validation error: ' . $e->getMessage());
            $this->logAttempt($user, $code, false, 'An error occurred while validating the coupon');
            return [
                'success' => false,
                'message' => 'An error occurred while validating the coupon'
            ];
        }
    }

    private function isValid($coupon)
    {
        $now = Carbon::now();
        if (!$coupon->is_active) {
            return false;
        }
        if ($coupon->valid_from && $now->lt($coupon->valid_from)) {
            return false;
        }
        if ($coupon->valid_until && $now->gt($coupon->valid_until)) {
            return false;
        }
        return true;
    }

    private function hasUserUsedCoupon($user, $coupon)
    {
        return CouponUsage::where('user_id', $user->id)
                          ->where('coupon_id', $coupon->id)
                          ->exists();
    }

    private function isUsageLimitReached($coupon)
    {
        if ($coupon->usage_limit === null) {
            return false;
        }

        $usageCount = CouponUsage::where('coupon_id', $coupon->id)->count();
        return $usageCount >= $coupon->usage_limit;
    }

    private function redeemCoupon($user, $coupon, $context)
    {
        try {
            DB::beginTransaction();

            // Record coupon usage
            CouponUsage::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon->id,
                'used_at' => Carbon::now(),
                'is_shop' => $context['is_shop'] ?? false
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Coupon redemption error: ' . $e->getMessage());
            return false;
        }
    }

    protected function logAttempt(User $user, string $code, bool $success, string $message)
    {
        // You can expand this to use a dedicated CouponUsageLog model/table
        Log::info('Coupon usage attempt', [
            'user_id' => $user->id,
            'code' => $code,
            'success' => $success,
            'message' => $message,
        ]);
    }

    private function getCreatorDiscount($creator)
    {
        $rank = Creator::where('points', '>', $creator->points)->count() + 1;
        
        return match(true) {
            $rank === 1 => 50,
            $rank <= 3 => 40,
            $rank <= 10 => 30,
            default => 20
        };
    }

    public function validateCoupon($code, $user = null, $context = [])
    {
        try {
            $coupon = Coupon::where('code', $code)->first();
            if (!$coupon) {
                $this->logAttempt($user, $code, false, 'Coupon not found');
                return ['success' => false, 'message' => 'Coupon not found'];
            }

            // Validate coupon
            if (!$this->isValid($coupon)) {
                $this->logAttempt($user, $code, false, 'Invalid or expired coupon code');
                return ['success' => false, 'message' => 'Invalid or expired coupon code'];
            }

            // Check if user has already used this coupon
            if ($this->hasUserUsedCoupon($user, $coupon)) {
                $this->logAttempt($user, $code, false, 'You have already used this coupon');
                return ['success' => false, 'message' => 'You have already used this coupon'];
            }

            // Check if coupon usage limit is reached
            if ($this->isUsageLimitReached($coupon)) {
                $this->logAttempt($user, $code, false, 'Coupon usage limit reached');
                return ['success' => false, 'message' => 'Coupon usage limit reached'];
            }

            // Redeem coupon
            $redeemed = $this->redeemCoupon($user, $coupon, $context);
            if (!$redeemed) {
                $this->logAttempt($user, $code, false, 'Failed to redeem coupon');
                return ['success' => false, 'message' => 'Failed to redeem coupon'];
            }

            // --- Creator referral/earnings logic ---
            // If coupon has a campaign_name that matches a creator code, apply dynamic discount
            if ($coupon->campaign_name) {
                $creator = \App\Models\Creator::where('code', $coupon->campaign_name)->first();
                if ($creator) {
                    $creator->referral_count = ($creator->referral_count ?? 0) + 1;
                    $creator->earnings = ($creator->earnings ?? 0) + 1;
                    $creator->points = ($creator->points ?? 0) + 10 + 1;
                    $creator->save();

                    // Apply dynamic discount based on creator's rank
                    $discount = $this->getCreatorDiscount($creator);
                    $discountType = 'fixed';
                }
            }
            // --- End creator logic ---

            $this->logAttempt($user, $code, true, 'Coupon redeemed');

            return [
                'success' => true,
                'message' => 'Coupon applied successfully.',
                'discount' => $discount,
                'discount_type' => $discountType,
                'coupon' => $coupon,
            ];

        } catch (\Exception $e) {
            \Log::error('Coupon validation error: ' . $e->getMessage());
            $this->logAttempt($user, $code, false, 'An error occurred while validating the coupon');
            return [
                'success' => false,
                'message' => 'An error occurred while validating the coupon'
            ];
        }
    }
} 