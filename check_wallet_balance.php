<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;

echo "💰 Checking Wallet Balance\n";
echo "==========================\n\n";

// Get user with ID 1 (from logs)
$user = User::find(1);

if (!$user) {
    echo "❌ User not found!\n";
    exit(1);
}

echo "👤 User: {$user->name} (ID: {$user->id})\n\n";

// Get wallet
$wallet = $user->wallet;

if (!$wallet) {
    echo "❌ No wallet found for this user!\n";
    exit(1);
}

echo "💳 Wallet Details:\n";
echo "  Balance: Rs. " . number_format($wallet->credits_balance, 2) . "\n";
echo "  Total Earned: Rs. " . number_format($wallet->total_credits_earned, 2) . "\n";
echo "  Total Spent: Rs. " . number_format($wallet->total_credits_spent, 2) . "\n";
echo "  Account Number: {$wallet->account_number}\n\n";

// Get recent transactions
$transactions = WalletTransaction::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "📊 Recent Transactions (Last 10):\n";
echo "==================================\n\n";

if ($transactions->count() > 0) {
    foreach ($transactions as $tx) {
        $type = strtoupper($tx->type);
        $amount = number_format($tx->credits_amount, 2);
        $before = number_format($tx->credits_balance_before, 2);
        $after = number_format($tx->credits_balance_after, 2);
        
        echo "{$tx->created_at->format('Y-m-d H:i:s')}\n";
        echo "  Type: {$type}\n";
        echo "  Amount: Rs. {$amount}\n";
        echo "  Before: Rs. {$before}\n";
        echo "  After: Rs. {$after}\n";
        echo "  Description: {$tx->description}\n";
        echo "  Status: {$tx->status}\n";
        echo "  ---\n";
    }
} else {
    echo "  No transactions found\n";
}

