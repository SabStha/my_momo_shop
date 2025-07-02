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
        // Add deleted_at to badge_classes
        Schema::table('badge_classes', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to badge_ranks
        Schema::table('badge_ranks', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to badge_tiers
        Schema::table('badge_tiers', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to user_badges
        Schema::table('user_badges', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to badge_progress
        Schema::table('badge_progress', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to ama_credits
        Schema::table('ama_credits', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to ama_credit_transactions
        Schema::table('ama_credit_transactions', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to credit_tasks
        Schema::table('credit_tasks', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to user_task_completions
        Schema::table('user_task_completions', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to credit_rewards
        Schema::table('credit_rewards', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add deleted_at to user_reward_redemptions
        Schema::table('user_reward_redemptions', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove deleted_at from user_reward_redemptions
        Schema::table('user_reward_redemptions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from credit_rewards
        Schema::table('credit_rewards', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from user_task_completions
        Schema::table('user_task_completions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from credit_tasks
        Schema::table('credit_tasks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from ama_credit_transactions
        Schema::table('ama_credit_transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from ama_credits
        Schema::table('ama_credits', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from badge_progress
        Schema::table('badge_progress', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from user_badges
        Schema::table('user_badges', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from badge_tiers
        Schema::table('badge_tiers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from badge_ranks
        Schema::table('badge_ranks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        // Remove deleted_at from badge_classes
        Schema::table('badge_classes', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}; 