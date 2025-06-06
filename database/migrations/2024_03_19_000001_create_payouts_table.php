<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('payment_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payouts');
    }
}; 