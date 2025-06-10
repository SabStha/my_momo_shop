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
        // Create roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name')->default('web');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('name');
            $table->index('guard_name');
        });

        // Create permissions table
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('guard_name')->default('web');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('name');
            $table->index('guard_name');
        });

        // Create role_has_permissions table
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');

            $table->primary(['permission_id', 'role_id']);
        });

        // Create model_has_roles table
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->primary(['role_id', 'model_type', 'model_id']);
            $table->index(['model_type', 'model_id']);
        });

        // Create model_has_permissions table
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->primary(['permission_id', 'model_type', 'model_id']);
            $table->index(['model_type', 'model_id']);
        });

        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('referral_code')->unique();
            $table->string('referred_by')->nullable();
            $table->decimal('points', 10, 2)->default(0);
            $table->string('role')->default('customer'); // customer, admin, employee, creator
            $table->boolean('is_admin')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('name');
            $table->index('email');
            $table->index('phone');
            $table->index('referral_code');
            $table->index('referred_by');
            $table->index('role');
            $table->index('is_admin');
        });

        // Create creators table
        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique();
            $table->text('bio')->nullable();
            $table->integer('points')->default(0);
            $table->integer('referral_count')->default(0);
            $table->string('avatar')->nullable();
            $table->decimal('earnings', 10, 2)->default(0);
            $table->integer('additional_discount')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('user_id');
            $table->index('code');
        });

        // Create user_settings table
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('preferences')->nullable();
            $table->json('notifications')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('user_id');
        });

        // Create password_reset_tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Create sessions table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Create notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_settings');
        Schema::dropIfExists('creators');
        Schema::dropIfExists('users');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
