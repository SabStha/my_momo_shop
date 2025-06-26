<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('campaign_triggers', function (Blueprint $table) {
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('action_taken')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->decimal('revenue_generated', 10, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('campaign_triggers', function (Blueprint $table) {
            $table->dropColumn([
                'campaign_id',
                'status',
                'action_taken',
                'opened_at',
                'clicked_at',
                'revenue_generated'
            ]);
        });
    }
}; 