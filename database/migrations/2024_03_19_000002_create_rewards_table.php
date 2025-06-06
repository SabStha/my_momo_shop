<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['monthly', 'referral', 'special'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->date('month');
            $table->boolean('claimed')->default(false);
            $table->timestamp('claimed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rewards');
    }
}; 