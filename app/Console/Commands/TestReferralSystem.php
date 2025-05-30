<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Creator;
use App\Models\Referral;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\OrderPlaced;

class TestReferralSystem extends Command
{
    protected $signature = 'test:referral';
    protected $description = 'Test the referral system by simulating a referred user placing an order';

    public function handle()
    {
        try {
            DB::beginTransaction();

            // 1. Create or find a creator
            $creator = Creator::first();
            if (!$creator) {
                $creator = Creator::create([
                    'user_id' => User::first()->id,
                    'code' => 'TEST' . strtoupper(uniqid()),
                    'points' => 0,
                    'earnings' => 0,
                    'referral_count' => 0
                ]);
            }
            $this->info("Creator found/created: {$creator->code}");

            // 2. Create or find a referred user
            $referredUser = User::where('email', 'test@example.com')->first();
            if (!$referredUser) {
                $referredUser = User::create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password')
                ]);
            }
            $this->info("Referred user found/created: {$referredUser->email}");

            // 3. Create referral if it doesn't exist
            $referral = Referral::where('referred_user_id', $referredUser->id)->first();
            if (!$referral) {
                $referral = Referral::create([
                    'creator_id' => $creator->user_id,
                    'referred_user_id' => $referredUser->id,
                    'code' => $creator->code,
                    'status' => 'signed_up',
                    'order_count' => 0
                ]);
            }
            $this->info("Referral found/created: {$referral->id}");

            // 4. Create a test order
            $product = Product::first();
            if (!$product) {
                $product = Product::create([
                    'name' => 'Test Product',
                    'price' => 100,
                    'description' => 'Test product for referral system'
                ]);
            }

            $order = Order::create([
                'order_number' => 'TEST-' . strtoupper(uniqid()),
                'type' => 'dine-in',
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'amount_received' => 150,
                'change' => 50,
                'total_amount' => 100,
                'tax_amount' => 13,
                'grand_total' => 113,
                'user_id' => $referredUser->id
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'item_name' => $product->name,
                'quantity' => 1,
                'price' => 100,
                'subtotal' => 100
            ]);

            $this->info("Test order created: {$order->order_number}");

            // Fire the OrderPlaced event to trigger referral logic
            event(new OrderPlaced($order));

            // 5. Check results
            $creator->refresh();
            $referral->refresh();

            $this->info("\nResults:");
            $this->info("Creator points: {$creator->points}");
            $this->info("Creator earnings: {$creator->earnings}");
            $this->info("Referral order count: {$referral->order_count}");
            $this->info("Referral status: {$referral->status}");

            // Check for discount coupon
            $userCoupon = \App\Models\UserCoupon::where('user_id', $referredUser->id)
                ->whereNull('used_at')
                ->latest()
                ->first();

            if ($userCoupon) {
                $this->info("Discount coupon created: {$userCoupon->coupon->code}");
                $this->info("Discount amount: Rs {$userCoupon->coupon->value}");
            }

            // Check for creator earnings record
            $earning = \App\Models\CreatorEarning::where('creator_id', $creator->id)
                ->latest()
                ->first();

            if ($earning) {
                $this->info("Creator earning record created:");
                $this->info("Amount: Rs {$earning->amount}");
                $this->info("Type: {$earning->type}");
                $this->info("Expires at: {$earning->expires_at}");
            }

            DB::commit();
            $this->info("\nTest completed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
            Log::error("Referral test failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 