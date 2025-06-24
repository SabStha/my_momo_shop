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
        Schema::create('investor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('investor_id')->constrained()->onDelete('cascade');
            $table->foreignId('investment_id')->nullable()->constrained('investor_investments')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->timestamp('payout_date');
            $table->enum('payout_type', ['dividend', 'interest', 'principal', 'profit_share'])->default('profit_share');
            $table->timestamp('period_start')->nullable();
            $table->timestamp('period_end')->nullable();
            $table->enum('status', ['pending', 'processed', 'paid', 'failed'])->default('pending');
            $table->enum('payment_method', ['bank_transfer', 'check', 'cash', 'digital_wallet'])->default('bank_transfer');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('processed_at')->nullable();
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->string('currency', 3)->default('NPR');
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_payouts');
    }
};
