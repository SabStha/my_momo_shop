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
        Schema::table('merchandises', function (Blueprint $table) {
            if (!Schema::hasColumn('merchandises', 'model')) {
                $table->string('model')->default('classic')->after('category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchandises', function (Blueprint $table) {
            if (Schema::hasColumn('merchandises', 'model')) {
                $table->dropColumn('model');
            }
        });
    }
};
