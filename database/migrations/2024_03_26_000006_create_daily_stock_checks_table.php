<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('daily_stock_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_checked', 10, 2);
            $table->date('checked_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent duplicate checks for the same item on the same day
            $table->unique(['inventory_item_id', 'checked_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_stock_checks');
    }
}; 