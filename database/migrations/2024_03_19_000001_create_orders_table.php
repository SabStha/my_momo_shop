<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Optional relation to user or guest
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();

            // Order type and table relation (for dine-in)
            $table->enum('type', ['dine-in', 'takeaway', 'online']);
            $table->unsignedBigInteger('table_id')->nullable();

            // Core monetary fields
            $table->decimal('total', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('amount_received', 10, 2)->nullable();
            $table->decimal('change', 10, 2)->nullable();

            // Payment and status
            $table->string('payment_method')->nullable()->default('cash');
            $table->string('payment_status')->default('pending');
            $table->string('status')->default('pending');

            // Addresses
            $table->string('shipping_address')->nullable()->default('N/A');
            $table->string('billing_address')->nullable()->default('N/A');

            $table->timestamps();

            // Optional: add foreign key constraints
            // $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            // $table->foreign('table_id')->references('id')->on('tables')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
