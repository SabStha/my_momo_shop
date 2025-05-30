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
        Schema::table('creators', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('code');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_menu_highlight')->default(false)->after('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creators', function (Blueprint $table) {
            $table->dropColumn('bio');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_menu_highlight');
        });
    }
}; 