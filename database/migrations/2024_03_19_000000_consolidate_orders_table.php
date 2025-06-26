<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('table_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('wallet_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('order_number')->unique();
            $table->string('order_type')->default('dine_in'); // dine_in, takeaway, delivery
            $table->string('status')->default('pending');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->string('payment_status')->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->decimal('amount_received', 10, 2)->nullable();
            $table->decimal('change', 10, 2)->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('wallet_payment', 10, 2)->default(0);
            $table->decimal('cash_payment', 10, 2)->default(0);
            $table->json('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for frequently queried columns
            $table->index('order_number');
            $table->index('status');
            $table->index('payment_status');
            $table->index('order_type');
            $table->index('branch_id');
            $table->index('user_id');
            $table->index('created_by');
            $table->index('paid_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}; 