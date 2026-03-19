<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_drawers', function (Blueprint $table) {
            $table->integer('dine_in_count')->default(0)->after('total_sales');
            $table->integer('takeaway_count')->default(0)->after('dine_in_count');
            $table->integer('online_count')->default(0)->after('takeaway_count');
        });
    }

    public function down(): void
    {
        Schema::table('cash_drawers', function (Blueprint $table) {
            $table->dropColumn(['dine_in_count', 'takeaway_count', 'online_count']);
        });
    }
};
