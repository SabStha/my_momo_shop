<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CreditsAccount;
use App\Models\CreditsTransaction;

class UpdateWalletsToCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credits:update-wallets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing wallet data to work with the new credits system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting wallet to credits conversion...');

        try {
            // Check if credits_accounts table exists
            if (!DB::getSchemaBuilder()->hasTable('credits_accounts')) {
                $this->error('Credits accounts table does not exist. Please run migrations first.');
                return 1;
            }

            // Check if there are any existing credits accounts
            $existingCredits = CreditsAccount::count();
            if ($existingCredits > 0) {
                $this->warn("Found {$existingCredits} existing credits accounts. Skipping conversion.");
                return 0;
            }

            // Check if there are any wallets to convert
            if (!DB::getSchemaBuilder()->hasTable('wallets')) {
                $this->info('No wallets table found. Conversion not needed.');
                return 0;
            }

            $walletCount = DB::table('wallets')->count();
            if ($walletCount === 0) {
                $this->info('No wallets found to convert.');
                return 0;
            }

            $this->info("Found {$walletCount} wallets to convert.");

            // Convert wallets to credits accounts
            $this->convertWallets();

            // Convert wallet transactions to credits transactions
            $this->convertTransactions();

            $this->info('Wallet to credits conversion completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Error during conversion: ' . $e->getMessage());
            return 1;
        }
    }

    private function convertWallets()
    {
        $this->info('Converting wallets to credits accounts...');

        $wallets = DB::table('wallets')->get();
        $bar = $this->output->createProgressBar($wallets->count());

        foreach ($wallets as $wallet) {
            // Convert currency amounts to credits (1 credit = 1 point)
            $creditsBalance = (int) ($wallet->balance ?? 0);
            $totalCreditsEarned = (int) ($wallet->total_earned ?? 0);
            $totalCreditsSpent = (int) ($wallet->total_spent ?? 0);

            CreditsAccount::create([
                'user_id' => $wallet->user_id,
                'account_number' => $wallet->wallet_number ?? CreditsAccount::generateAccountNumber(),
                'credits_barcode' => $wallet->barcode ?? CreditsAccount::generateCreditsBarcode(),
                'credits_balance' => $creditsBalance,
                'total_credits_earned' => $totalCreditsEarned,
                'total_credits_spent' => $totalCreditsSpent,
                'is_active' => $wallet->is_active ?? true,
                'created_at' => $wallet->created_at,
                'updated_at' => $wallet->updated_at
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Wallets converted successfully!');
    }

    private function convertTransactions()
    {
        $this->info('Converting wallet transactions to credits transactions...');

        if (!DB::getSchemaBuilder()->hasTable('wallet_transactions')) {
            $this->info('No wallet transactions table found. Skipping transaction conversion.');
            return;
        }

        $transactions = DB::table('wallet_transactions')->get();
        
        if ($transactions->count() === 0) {
            $this->info('No wallet transactions found to convert.');
            return;
        }

        $bar = $this->output->createProgressBar($transactions->count());

        foreach ($transactions as $transaction) {
            // Convert currency amounts to credits
            $creditsAmount = (int) ($transaction->amount ?? 0);
            $creditsBalanceBefore = (int) ($transaction->balance_before ?? 0);
            $creditsBalanceAfter = (int) ($transaction->balance_after ?? 0);

            CreditsTransaction::create([
                'credits_account_id' => $transaction->wallet_id,
                'user_id' => $transaction->user_id,
                'branch_id' => $transaction->branch_id,
                'credits_amount' => $creditsAmount,
                'type' => $transaction->type,
                'description' => $transaction->description,
                'status' => $transaction->status ?? 'completed',
                'performed_by' => $transaction->performed_by,
                'performed_by_branch_id' => $transaction->performed_by_branch_id,
                'order_id' => $transaction->order_id,
                'reference_number' => $transaction->reference_number,
                'credits_balance_before' => $creditsBalanceBefore,
                'credits_balance_after' => $creditsBalanceAfter,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at
            ]);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Transactions converted successfully!');
    }
}
