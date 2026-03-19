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
        Schema::create('display_ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['upload', 'youtube', 'vimeo']);
            $table->string('file_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('youtube_id')->nullable();
            $table->string('vimeo_id')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('display_ads');
    }
};
