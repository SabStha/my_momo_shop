<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreatorPointsService
{
    /**
     * Award points to a creator and update their wallet
     *
     * @param Creator $creator
     * @param int $points
     * @param string $description
     * @return void
     */
    public function awardPoints(Creator $creator, int $points, string $description = 'Points earned')
    {
        try {
            DB::beginTransaction();

            // Update creator points
            $oldPoints = $creator->points;
            $creator->points += $points;
            $creator->save();

            // Get or create wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $creator->user_id],
                ['balance' => 0]
            );

            // Add points to wallet (1 point = $1)
            $amount = $points;
            
            // Create transaction record
            $wallet->transactions()->create([
                'wallet_id' => $wallet->id,
                'user_id' => $creator->user_id,
                'type' => 'credit',
                'amount' => $amount,
                'description' => $description
            ]);

            // Update wallet balance
            $wallet->balance += $amount;
            $wallet->save();

            DB::commit();

            Log::info('Creator points and wallet updated', [
                'creator_id' => $creator->id,
                'old_points' => $oldPoints,
                'new_points' => $creator->points,
                'points_awarded' => $points,
                'wallet_amount' => $amount,
                'new_balance' => $wallet->balance
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to award points and update wallet', [
                'creator_id' => $creator->id,
                'points' => $points,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 