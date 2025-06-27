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
        Schema::table('offers', function (Blueprint $table) {
            $table->string('type')->nullable()->after('max_discount');
            $table->string('target_audience')->default('all')->after('type');
            $table->boolean('ai_generated')->default(false)->after('target_audience');
            $table->text('ai_reasoning')->nullable()->after('ai_generated');
            $table->foreignId('branch_id')->nullable()->after('ai_reasoning')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->after('branch_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['type', 'target_audience', 'ai_generated', 'ai_reasoning', 'branch_id', 'user_id']);
        });
    }
};
