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
        Schema::table('orders', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['wallet_id']);
            
            // Rename the column
            $table->renameColumn('wallet_id', 'credits_account_id');
            
            // Add the new foreign key constraint
            $table->foreign('credits_account_id')->references('id')->on('credits_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['credits_account_id']);
            
            // Rename the column back
            $table->renameColumn('credits_account_id', 'wallet_id');
            
            // Add the old foreign key constraint back
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('set null');
        });
    }
};
