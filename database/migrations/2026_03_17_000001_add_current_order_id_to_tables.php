<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            // Tracks which order is currently using this table (nullable, no FK to avoid
            // conflicts with Order SoftDeletes — managed at application level).
            $table->unsignedBigInteger('current_order_id')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('current_order_id');
        });
    }
};
