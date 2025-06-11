<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterStatusEnumOnReferralsTable extends Migration
{
    public function up()
    {
        // For SQLite, we need to recreate the table
        Schema::table('referrals', function (Blueprint $table) {
            // Create a temporary table with the new structure
            Schema::create('referrals_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('referred_user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('code');
                $table->enum('status', ['signed_up', 'ordered', 'completed'])->default('signed_up');
                $table->integer('order_count')->default(0);
                $table->timestamps();

                // Only make referred_user_id unique to prevent multiple referrals for the same user
                $table->unique('referred_user_id');
            });

            // Copy data from the old table to the new one
            DB::statement('INSERT INTO referrals_temp (id, creator_id, referred_user_id, code, status, order_count, created_at, updated_at) SELECT id, creator_id, referred_user_id, code, status, order_count, created_at, updated_at FROM referrals');

            // Drop the old table
            Schema::drop('referrals');

            // Rename the new table to the original name
            Schema::rename('referrals_temp', 'referrals');
        });
    }

    public function down()
    {
        // For SQLite, we need to recreate the table
        Schema::table('referrals', function (Blueprint $table) {
            // Create a temporary table with the old structure
            Schema::create('referrals_temp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('referred_user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('code');
                $table->enum('status', ['signed_up', 'ordered'])->default('signed_up');
                $table->integer('order_count')->default(0);
                $table->timestamps();

                // Only make referred_user_id unique to prevent multiple referrals for the same user
                $table->unique('referred_user_id');
            });

            // Copy data from the new table to the old one
            DB::statement('INSERT INTO referrals_temp (id, creator_id, referred_user_id, code, status, order_count, created_at, updated_at) SELECT id, creator_id, referred_user_id, code, status, order_count, created_at, updated_at FROM referrals');

            // Drop the new table
            Schema::drop('referrals');

            // Rename the old table back to the original name
            Schema::rename('referrals_temp', 'referrals');
        });
    }
}
