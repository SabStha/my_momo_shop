<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename wallets table to credits_accounts
        Schema::rename('wallets', 'credits_accounts');
        
        // Rename wallet_transactions table to credits_transactions
        Schema::rename('wallet_transactions', 'credits_transactions');
        
        // Update credits_accounts table columns
        Schema::table('credits_accounts', function (Blueprint $table) {
            // Rename columns to reflect credits system
            $table->renameColumn('wallet_number', 'account_number');
            $table->renameColumn('balance', 'credits_balance');
            $table->renameColumn('total_earned', 'total_credits_earned');
            $table->renameColumn('total_spent', 'total_credits_spent');
            $table->renameColumn('barcode', 'credits_barcode');
        });
        
        // Update credits_transactions table columns
        Schema::table('credits_transactions', function (Blueprint $table) {
            $table->renameColumn('wallet_id', 'credits_account_id');
            $table->renameColumn('amount', 'credits_amount');
            $table->renameColumn('balance_before', 'credits_balance_before');
            $table->renameColumn('balance_after', 'credits_balance_after');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert credits_transactions table columns
        Schema::table('credits_transactions', function (Blueprint $table) {
            $table->renameColumn('credits_account_id', 'wallet_id');
            $table->renameColumn('credits_amount', 'amount');
            $table->renameColumn('credits_balance_before', 'balance_before');
            $table->renameColumn('credits_balance_after', 'balance_after');
        });
        
        // Revert credits_accounts table columns
        Schema::table('credits_accounts', function (Blueprint $table) {
            $table->renameColumn('account_number', 'wallet_number');
            $table->renameColumn('credits_balance', 'balance');
            $table->renameColumn('total_credits_earned', 'total_earned');
            $table->renameColumn('total_credits_spent', 'total_spent');
            $table->renameColumn('credits_barcode', 'barcode');
        });
        
        // Rename tables back
        Schema::rename('credits_accounts', 'wallets');
        Schema::rename('credits_transactions', 'wallet_transactions');
    }
};
