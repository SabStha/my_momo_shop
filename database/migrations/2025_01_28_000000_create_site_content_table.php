<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('site_content', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('title');
            $table->text('content');
            $table->string('type')->default('text'); // text, html, image, json, boolean
            $table->string('section')->default('general'); // home, auth, menu, admin, general, bulk
            $table->string('component')->nullable(); // hero, banner, etc.
            $table->string('platform')->default('all'); // all, web, mobile
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['section', 'platform', 'is_active']);
            $table->index(['key', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_content');
    }
};






