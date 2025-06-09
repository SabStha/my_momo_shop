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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Drop existing foreign keys if they exist
            $foreignKeys = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys('wallet_transactions');

            foreach ($foreignKeys as $foreignKey) {
                if (in_array($foreignKey->getName(), [
                    'wallet_transactions_performed_by_foreign',
                    'wallet_transactions_performed_by_branch_id_foreign'
                ])) {
                    $table->dropForeign($foreignKey->getName());
                }
            }

            // Add foreign key constraints
            $table->foreign('performed_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('performed_by_branch_id')
                ->references('id')
                ->on('branches')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            // Drop foreign keys if they exist
            $foreignKeys = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableForeignKeys('wallet_transactions');

            foreach ($foreignKeys as $foreignKey) {
                if (in_array($foreignKey->getName(), [
                    'wallet_transactions_performed_by_foreign',
                    'wallet_transactions_performed_by_branch_id_foreign'
                ])) {
                    $table->dropForeign($foreignKey->getName());
                }
            }
        });
    }
};
