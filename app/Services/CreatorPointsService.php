<?php

namespace App\Services;

use App\Models\Creator;
use App\Models\Wallet;
use App\Models\WalletTransaction;
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
     * @param Creator|null $referral
     * @return void
     */
    public function awardPoints(Creator $creator, int $points, string $description = '', $referral = null)
    {
        Log::info('Starting points award process', [
            'creator_id' => $creator->id,
            'points_to_award' => $points,
            'current_points' => $creator->points,
            'referral_id' => $referral ? $referral->id : null
        ]);

        try {
            DB::beginTransaction();
            Log::info('Transaction started');

            // Update creator's points
            $creator->points += $points;
            $creator->save();

            Log::info('Creator points updated', [
                'creator_id' => $creator->id,
                'points_added' => $points,
                'new_points_total' => $creator->points
            ]);

            // Update referral points if provided
            if ($referral) {
                $referral->points += $points;
                $referral->save();
                Log::info('Referral points updated', [
                    'referral_id' => $referral->id,
                    'points_added' => $points,
                    'new_points_total' => $referral->points
                ]);

                // Update referred user's wallet if this is their first order
                if ($referral->order_count === 1) {
                    $referredUser = $referral->referredUser;
                    if ($referredUser) {
                        $wallet = \App\Models\Wallet::firstOrCreate(
                            ['user_id' => $referredUser->id],
                            ['balance' => 0]
                        );

                        // Create transaction record for referred user
                        $transaction = \App\Models\WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'user_id' => $referredUser->id,
                            'amount' => 50, // Rs. 50 for first order
                            'type' => 'credit',
                            'description' => 'Welcome bonus for first order',
                            'status' => 'completed',
                            'reference_number' => 'REF-' . strtoupper(uniqid())
                        ]);

                        // Update wallet balance
                        $wallet->balance += 50;
                        $wallet->save();

                        Log::info('Referred user wallet updated', [
                            'user_id' => $referredUser->id,
                            'amount_added' => 50,
                            'new_balance' => $wallet->balance
                        ]);
                    }
                }
            }

            // Get or create wallet for the creator
            $wallet = \App\Models\Wallet::firstOrCreate(
                ['user_id' => $creator->user_id],
                ['balance' => 0]
            );

            Log::info('Wallet found/created', [
                'wallet_id' => $wallet->id,
                'user_id' => $creator->user_id
            ]);

            // Create transaction record
            $transaction = \App\Models\WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'user_id' => $creator->user_id,
                'branch_id' => null,
                'amount' => $points,
                'type' => 'credit',
                'description' => $description,
                'status' => 'completed',
                'performed_by' => $creator->user_id,
                'performed_by_branch_id' => null,
                'order_id' => null,
                'reference_number' => 'REF-' . strtoupper(uniqid()),
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance + $points
            ]);

            Log::info('Wallet transaction created', [
                'transaction_id' => $transaction->id,
                'wallet_id' => $wallet->id,
                'amount' => $points,
                'type' => 'credit'
            ]);

            // Update wallet balance
            $wallet->balance += $points;
            $wallet->save();

            Log::info('Wallet balance updated', [
                'creator_id' => $creator->id,
                'wallet_id' => $wallet->id,
                'amount_added' => $points,
                'new_wallet_balance' => $wallet->balance
            ]);

            DB::commit();
            Log::info('Transaction committed successfully');

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in points award process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'creator_id' => $creator->id,
                'points' => $points
            ]);
            throw $e;
        }
    }
} 