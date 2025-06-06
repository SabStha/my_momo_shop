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
        if (!Schema::hasColumn('creators', 'bio')) {
        Schema::table('creators', function (Blueprint $table) {
                $table->text('bio')->nullable()->after('code');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('creators', 'bio')) {
        Schema::table('creators', function (Blueprint $table) {
                $table->dropColumn('bio');
        });
        }
    }
};
