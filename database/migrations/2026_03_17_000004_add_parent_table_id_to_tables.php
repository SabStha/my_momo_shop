<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            // Points to the original table when this record is a sub-table created
            // by a pre-order split or a table absorbed by a combine operation.
            $table->unsignedBigInteger('parent_table_id')->nullable()->after('current_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('parent_table_id');
        });
    }
};
