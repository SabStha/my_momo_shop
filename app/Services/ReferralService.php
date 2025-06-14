<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Creator;
use App\Models\Referral;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Coupon;
use App\Models\UserCoupon;
use App\Models\CreatorEarning;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    protected $settings;

    public function __construct()
    {
        $this->loadSettings();
    }

    protected function loadSettings()
    {
        $this->settings = Cache::remember('settings', 3600, function () {
            return Setting::whereIn('key', [
                'referral_welcome_bonus',
                'referral_first_order_bonus',
                'referral_subsequent_order_bonus',
                'creator_referral_bonus',
                'creator_first_order_bonus',
                'creator_subsequent_order_bonus',
                'max_referral_orders'
            ])->pluck('value', 'key')->toArray();
        });

        Log::info('Loaded referral settings', ['settings' => $this->settings]);
    }

    public function processNewReferral($user, $creator)
    {
        Log::info('Processing new referral', [
            'user_id' => $user->id,
            'creator_id' => $creator->id,
            'settings' => $this->settings
        ]);

        try {
            DB::beginTransaction();

            // Award points to creator
            $pointsToAward = (int)$this->settings['creator_referral_bonus'];
            $creator->addPoints($pointsToAward, 'Points earned for new referral');
            
            // Update referral count based on actual referrals
            $referralCount = Referral::where('creator_id', $creator->id)->count();
            $creator->referral_count = $referralCount;
            $creator->save();

            Log::info('Updated creator referral count', [
                'creator_id' => $creator->id,
                'new_referral_count' => $creator->referral_count,
                'points_awarded' => $pointsToAward
            ]);

            // Add welcome bonus to user's wallet
            $userWallet = Wallet::where('user_id', $user->id)->first();
            if (!$userWallet) {
                Log::error('User wallet not found', ['user_id' => $user->id]);
                DB::rollBack();
                return;
            }

            // Add bonus to creator's wallet
            $creatorWallet = Wallet::where('user_id', $creator->user_id)->first();
            if (!$creatorWallet) {
                Log::error('Creator wallet not found', ['creator_id' => $creator->id, 'user_id' => $creator->user_id]);
                DB::rollBack();
                return;
            }

            $welcomeBonus = (int)$this->settings['referral_welcome_bonus'];
            $creatorBonus = (int)$this->settings['creator_referral_bonus'];
            
            // Create transaction for user's wallet
            $userTransaction = WalletTransaction::create([
                'wallet_id' => $userWallet->id,
                'user_id' => $user->id,
                'branch_id' => $userWallet->branch_id,
                'amount' => $welcomeBonus,
                'type' => 'credit',
                'description' => 'Welcome bonus for registering with referral code',
                'status' => 'completed',
                'reference_number' => 'REF-' . strtoupper(uniqid()),
                'balance_before' => $userWallet->balance,
                'balance_after' => $userWallet->balance + $welcomeBonus
            ]);

            // Create transaction for creator's wallet
            $creatorTransaction = WalletTransaction::create([
                'wallet_id' => $creatorWallet->id,
                'user_id' => $creator->user_id,
                'branch_id' => $creatorWallet->branch_id,
                'amount' => $creatorBonus,
                'type' => 'credit',
                'description' => 'Bonus for new referral',
                'status' => 'completed',
                'reference_number' => 'CREF-' . strtoupper(uniqid()),
                'balance_before' => $creatorWallet->balance,
                'balance_after' => $creatorWallet->balance + $creatorBonus
            ]);

            // Update user's wallet
            $userWallet->balance = $userWallet->balance + $welcomeBonus;
            $userWallet->total_earned = $userWallet->total_earned + $welcomeBonus;
            $userWallet->save();

            // Update creator's wallet
            $creatorWallet->balance = $creatorWallet->balance + $creatorBonus;
            $creatorWallet->total_earned = $creatorWallet->total_earned + $creatorBonus;
            $creatorWallet->save();

            Log::info('Added welcome bonus to user wallet', [
                'user_id' => $user->id,
                'wallet_id' => $userWallet->id,
                'bonus_amount' => $welcomeBonus,
                'old_balance' => $userTransaction->balance_before,
                'new_balance' => $userWallet->balance,
                'transaction_id' => $userTransaction->id
            ]);

            Log::info('Added bonus to creator wallet', [
                'creator_id' => $creator->id,
                'user_id' => $creator->user_id,
                'wallet_id' => $creatorWallet->id,
                'bonus_amount' => $creatorBonus,
                'old_balance' => $creatorTransaction->balance_before,
                'new_balance' => $creatorWallet->balance,
                'transaction_id' => $creatorTransaction->id
            ]);

            // Create welcome coupon for the user
            $coupon = Coupon::create([
                'name' => 'Welcome Discount',
                'code' => 'WELCOME' . strtoupper(substr(md5(uniqid()), 0, 8)),
                'type' => 'fixed',
                'value' => (int)$this->settings['referral_first_order_bonus'],
                'min_order_amount' => 100,
                'max_uses' => 1,
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
                'description' => 'Welcome discount from referral'
            ]);

            UserCoupon::create([
                'user_id' => $user->id,
                'coupon_id' => $coupon->id,
                'used_at' => null
            ]);

            Log::info('Created welcome coupon', [
                'user_id' => $user->id,
                'coupon_id' => $coupon->id,
                'value' => $coupon->value
            ]);

            DB::commit();

            Log::info('Completed referral processing', [
                'user_id' => $user->id,
                'creator_id' => $creator->id,
                'points_awarded' => $pointsToAward,
                'user_welcome_bonus' => $welcomeBonus,
                'creator_bonus' => $creatorBonus,
                'user_wallet_balance' => $userWallet->balance,
                'creator_wallet_balance' => $creatorWallet->balance
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process referral', [
                'user_id' => $user->id,
                'creator_id' => $creator->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function processOrder($user, $referral, $order)
    {
        Log::info('Processing order for referral', [
            'user_id' => $user->id,
            'referral_id' => $referral->id,
            'order_id' => $order->id,
            'settings' => $this->settings
        ]);

        if (!$referral || $referral->order_count >= (int)$this->settings['max_referral_orders']) {
            Log::info('Skipping referral processing - max orders reached or no referral', [
                'referral_id' => $referral ? $referral->id : null,
                'order_count' => $referral ? $referral->order_count : 0,
                'max_orders' => (int)$this->settings['max_referral_orders']
            ]);
            return;
        }

        $creator = Creator::where('user_id', $referral->creator_id)->first();
        if (!$creator) {
            Log::warning('Creator not found for referral', [
                'referral_id' => $referral->id,
                'creator_id' => $referral->creator_id
            ]);
            return;
        }

        $referral->order_count = min($referral->order_count + 1, (int)$this->settings['max_referral_orders']);
        $referral->status = 'ordered';
        $referral->save();

        // Award points to creator
        $pointsToAward = $referral->order_count === 1 
            ? (int)$this->settings['creator_first_order_bonus']
            : (int)$this->settings['creator_subsequent_order_bonus'];
        
        $creator->addPoints($pointsToAward, 'Points earned for referred user order #' . $order->id);

        // Create discount coupon for user
        $discountAmount = $referral->order_count === 1 
            ? (int)$this->settings['referral_first_order_bonus']
            : (int)$this->settings['referral_subsequent_order_bonus'];

        $coupon = Coupon::create([
            'code' => 'REF' . strtoupper(uniqid()),
            'type' => 'fixed',
            'value' => $discountAmount,
            'min_order_amount' => 100,
            'max_uses' => 1,
            'expires_at' => now()->addMonths(3),
            'is_active' => true,
            'description' => $referral->order_count === 1 
                ? 'First order discount from referral'
                : 'Order discount from referral'
        ]);

        UserCoupon::create([
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'used_at' => null
        ]);

        Log::info('Created discount coupon for order', [
            'user_id' => $user->id,
            'coupon_id' => $coupon->id,
            'discount_amount' => $discountAmount,
            'order_count' => $referral->order_count
        ]);
    }
} 