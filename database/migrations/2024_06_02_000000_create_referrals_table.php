<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('creators')->onDelete('cascade');
            $table->foreignId('referred_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('coupon_code')->unique();
            $table->enum('status', ['pending', 'used', 'expired'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('referrals');
    }
}; 