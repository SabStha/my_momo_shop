<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pos_access_logs')) {
            Schema::create('pos_access_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('access_type'); // 'pos' or 'payment_manager'
                $table->string('action'); // 'login', 'logout', 'order', 'payment'
                $table->json('details')->nullable();
                $table->string('ip_address');
                $table->timestamps();
            });
        } else {
            // Add any missing columns
            Schema::table('pos_access_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('pos_access_logs', 'user_id')) {
                    $table->foreignId('user_id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('pos_access_logs', 'access_type')) {
                    $table->string('access_type')->after('user_id');
                }
                if (!Schema::hasColumn('pos_access_logs', 'action')) {
                    $table->string('action')->after('access_type');
                }
                if (!Schema::hasColumn('pos_access_logs', 'details')) {
                    $table->json('details')->nullable();
                }
                if (!Schema::hasColumn('pos_access_logs', 'ip_address')) {
                    $table->string('ip_address')->after('details');
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pos_access_logs');
    }
}; 