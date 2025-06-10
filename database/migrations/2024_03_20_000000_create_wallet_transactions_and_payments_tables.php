<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Create wallet_transactions table
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type'); // credit, debit
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference')->nullable();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status')->default('completed');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('wallet_id');
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('order_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_by');
            $table->index('approved_by');
        });

        // Create payments table
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->string('payment_method'); // cash, card, wallet, etc.
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('PHP');
            $table->string('status')->default('pending');
            $table->string('reference')->nullable();
            $table->json('payment_details')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('order_id');
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('payment_method');
            $table->index('status');
            $table->index('created_by');
            $table->index('approved_by');
        });
    }

    public function down() {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('wallet_transactions');
    }
}; 