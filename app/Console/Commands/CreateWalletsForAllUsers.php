<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Wallet;

class CreateWalletsForAllUsers extends Command
{
    protected $signature = 'wallets:create-for-all-users';
    protected $description = 'Create wallets for all users who do not have one';

    public function handle()
    {
        $users = User::all();
        $count = 0;
        foreach ($users as $user) {
            if (!$user->wallet) {
                Wallet::create(['user_id' => $user->id, 'balance' => 0]);
                $count++;
            }
        }
        $this->info("Created wallets for $count users.");
    }
} 