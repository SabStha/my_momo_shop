<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cash_drawers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->decimal('starting_amount', 10, 2);
            $table->decimal('current_balance', 10, 2)->default(0);
            $table->decimal('total_cash', 10, 2);
            $table->decimal('total_sales', 10, 2)->default(0);
            $table->json('denominations');
            $table->string('status')->default('closed');
            $table->timestamps();

            $table->unique(['branch_id', 'date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_drawers');
    }
}; 