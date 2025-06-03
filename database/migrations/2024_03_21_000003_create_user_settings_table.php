<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('user_settings')) {
            Schema::create('user_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->boolean('notify_orders')->default(true);
                $table->boolean('notify_offers')->default(true);
                $table->string('theme')->default('light');
                $table->string('language')->default('en');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_settings');
    }
}; 