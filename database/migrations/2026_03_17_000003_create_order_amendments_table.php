<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->foreignId('amended_by')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->json('items_removed')->nullable();
            $table->json('items_added')->nullable();
            $table->decimal('amount_change', 10, 2)->default(0);
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_amendments');
    }
};
