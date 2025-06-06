<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pos_access_logs', function (Blueprint $table) {
            // Add any missing columns
            if (!Schema::hasColumn('pos_access_logs', 'details')) {
                $table->json('details')->nullable();
            }
            if (!Schema::hasColumn('pos_access_logs', 'access_type')) {
                $table->string('access_type')->after('user_id');
            }
            if (!Schema::hasColumn('pos_access_logs', 'action')) {
                $table->string('action')->after('access_type');
            }
            if (!Schema::hasColumn('pos_access_logs', 'ip_address')) {
                $table->string('ip_address')->after('details');
            }
        });
    }

    public function down()
    {
        Schema::table('pos_access_logs', function (Blueprint $table) {
            // Remove columns if needed
            $table->dropColumn(['details', 'access_type', 'action', 'ip_address']);
        });
    }
}; 