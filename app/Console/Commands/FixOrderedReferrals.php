<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Referral;
use App\Models\Creator;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class FixOrderedReferrals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'referral:patch-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Patch referrals with orders: set status to ordered, update order_count, and award points to creators.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fixed = 0;
        $referrals = Referral::where('status', 'signed_up')
            ->whereNotNull('referred_user_id')
            ->get();

        foreach ($referrals as $referral) {
            $orderCount = Order::where('user_id', $referral->referred_user_id)->count();
            if ($orderCount > 0) {
                $referral->status = 'ordered';
                $referral->order_count = min($orderCount, 10);
                $referral->save();
                $creator = Creator::find($referral->creator_id);
                if ($creator) {
                    $creator->points += 5;
                    $creator->save();
                    Log::info("Referral #{$referral->id} upgraded to ordered. Creator #{$creator->id} awarded 5 points.");
                }
                $fixed++;
            }
        }
        $this->info("{$fixed} referrals were fixed.");
    }
} 