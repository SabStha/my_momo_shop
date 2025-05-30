<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\Referral;
use Illuminate\Support\Facades\Log;

class HandleReferralOrder
{
    public function handle(OrderPlaced $event)
    {
        $order = $event->order;
        $user = $order->user;

        // Find referral for this user
        $referral = Referral::where('referred_user_id', $user->id)
            ->where('status', 'signed_up')
            ->first();

        if ($referral) {
            $creator = $referral->creator;
            $orderCount = $referral->order_count ?? 0;

            // Update referral status and order count
            $referral->status = 'ordered';
            $referral->order_count = $orderCount + 1;
            $referral->save();

            // Award points to creator
            if ($orderCount === 0) {
                // First order
                $creator->points += 5;
                Log::info('First order points awarded to creator', [
                    'creator_id' => $creator->id,
                    'points' => $creator->points
                ]);
            } elseif ($orderCount < 10) {
                // Orders 2-10
                $creator->points += 5;
                Log::info('Order points awarded to creator', [
                    'creator_id' => $creator->id,
                    'order_count' => $orderCount + 1,
                    'points' => $creator->points
                ]);
            }
            $creator->save();

            // Award points to user
            if ($orderCount === 0) {
                // First order
                $user->points += 3;
                Log::info('First order points awarded to user', [
                    'user_id' => $user->id,
                    'points' => $user->points
                ]);
            } elseif ($orderCount < 10) {
                // Orders 2-10
                $user->points += 2;
                Log::info('Order points awarded to user', [
                    'user_id' => $user->id,
                    'order_count' => $orderCount + 1,
                    'points' => $user->points
                ]);
            }
            $user->save();
        }
    }
} 