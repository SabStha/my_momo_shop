<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add the columns if they don't exist
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transactions', 'performed_by')) {
                $table->unsignedBigInteger('performed_by')->nullable()->after('branch_id');
            }
            if (!Schema::hasColumn('wallet_transactions', 'performed_by_branch_id')) {
                $table->unsignedBigInteger('performed_by_branch_id')->nullable()->after('performed_by');
            }
            if (!Schema::hasColumn('wallet_transactions', 'status')) {
                $table->string('status')->after('description')->default('completed');
            }
            if (!Schema::hasColumn('wallet_transactions', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('status');
            }
        });

        // Add indexes if they don't exist
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (!Schema::hasIndex('wallet_transactions', ['branch_id', 'created_at'])) {
                $table->index(['branch_id', 'created_at']);
            }
            if (!Schema::hasIndex('wallet_transactions', ['performed_by', 'created_at'])) {
                $table->index(['performed_by', 'created_at']);
            }
        });

        // Update any NULL or empty reference numbers with unique values
        $isSQLite = Schema::getConnection()->getDriverName() === 'sqlite';
        
        if ($isSQLite) {
            // For SQLite, use PHP to generate UUIDs and update in chunks
            $transactions = DB::table('wallet_transactions')
                ->whereNull('reference_number')
                ->orWhere('reference_number', '')
                ->get(['id']);
            
            foreach ($transactions as $transaction) {
                DB::table('wallet_transactions')
                    ->where('id', $transaction->id)
                    ->update(['reference_number' => 'REF-' . Str::uuid()]);
            }
        } else {
            // For MySQL/PostgreSQL, use database functions
            DB::table('wallet_transactions')
                ->whereNull('reference_number')
                ->orWhere('reference_number', '')
                ->update(['reference_number' => DB::raw("CONCAT('REF-', UUID())")]);
        }

        // Now add the unique constraint
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unique('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Drop unique constraint if it exists
            if (Schema::hasIndex('wallet_transactions', 'wallet_transactions_reference_number_unique')) {
                $table->dropUnique('wallet_transactions_reference_number_unique');
            }

            // Drop columns
            $table->dropColumn([
                'performed_by',
                'performed_by_branch_id',
                'status',
                'reference_number'
            ]);

            // Drop indexes
            $table->dropIndex(['branch_id', 'created_at']);
            $table->dropIndex(['performed_by', 'created_at']);
        });
    }
};
