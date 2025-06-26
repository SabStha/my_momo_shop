<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create the table first without foreign keys
        Schema::create('campaign_triggers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('trigger_type'); // behavioral, scheduled, segment
            $table->json('trigger_condition');
            $table->string('campaign_type'); // email, sms, push
            $table->text('campaign_template');
            $table->unsignedBigInteger('segment_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('next_scheduled_at')->nullable();
            $table->string('frequency')->default('once'); // once, daily, weekly, monthly
            $table->integer('cooldown_period')->default(24); // in hours
            $table->timestamps();
            $table->softDeletes();
        });

        // Add foreign key constraints after table creation
        Schema::table('campaign_triggers', function (Blueprint $table) {
            $table->foreign('segment_id')->references('id')->on('customer_segments')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_triggers');
    }
}; 