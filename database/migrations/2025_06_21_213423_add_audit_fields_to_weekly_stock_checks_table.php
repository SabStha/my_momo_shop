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
        Schema::table('weekly_stock_checks', function (Blueprint $table) {
            // Audit trail fields
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('audit_notes')->nullable();
            $table->boolean('is_damaged')->default(false);
            $table->boolean('is_missing')->default(false);
            $table->string('image_path')->nullable();
            
            // Discrepancy tracking
            $table->decimal('system_stock', 10, 2)->nullable();
            $table->decimal('discrepancy_amount', 10, 2)->nullable();
            $table->decimal('discrepancy_value', 10, 2)->nullable();
            
            // Audit metadata
            $table->string('audit_session_id')->nullable();
            $table->timestamp('audit_started_at')->nullable();
            $table->timestamp('audit_completed_at')->nullable();
            
            // Indexes for performance
            $table->index(['branch_id', 'checked_at']);
            $table->index('audit_session_id');
            $table->index(['is_damaged', 'is_missing']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_stock_checks', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id', 'checked_at']);
            $table->dropIndex(['audit_session_id']);
            $table->dropIndex(['is_damaged', 'is_missing']);
            
            $table->dropColumn([
                'branch_id',
                'audit_notes',
                'is_damaged',
                'is_missing',
                'image_path',
                'system_stock',
                'discrepancy_amount',
                'discrepancy_value',
                'audit_session_id',
                'audit_started_at',
                'audit_completed_at'
            ]);
        });
    }
};
