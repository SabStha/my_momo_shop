<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('ingredients')->nullable()->after('description');
            $table->text('allergens')->nullable()->after('ingredients');
            $table->string('calories')->nullable()->after('allergens');
            $table->string('preparation_time')->nullable()->after('calories');
            $table->string('spice_level')->nullable()->after('preparation_time');
            $table->boolean('is_vegetarian')->default(false)->after('spice_level');
            $table->boolean('is_vegan')->default(false)->after('is_vegetarian');
            $table->boolean('is_gluten_free')->default(false)->after('is_vegan');
            $table->text('nutritional_info')->nullable()->after('is_gluten_free');
            $table->string('serving_size')->nullable()->after('nutritional_info');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'ingredients',
                'allergens',
                'calories',
                'preparation_time',
                'spice_level',
                'is_vegetarian',
                'is_vegan',
                'is_gluten_free',
                'nutritional_info',
                'serving_size'
            ]);
        });
    }
};