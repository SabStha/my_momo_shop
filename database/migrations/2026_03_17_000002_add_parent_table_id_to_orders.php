<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Tracks which table an order was split from (Phase 2 split feature).
            // Intentionally no FK constraint — orders use SoftDeletes and the
            // source table may be reused or deleted later.
            $table->unsignedBigInteger('parent_table_id')->nullable()->after('table_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('parent_table_id');
        });
    }
};
