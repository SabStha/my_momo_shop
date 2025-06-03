<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('offers')) {
            Schema::create('offers', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description');
                $table->decimal('discount', 5, 2);
                $table->timestamp('valid_from')->useCurrent();
                $table->timestamp('valid_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('code')->unique();
                $table->decimal('min_purchase', 10, 2)->nullable();
                $table->decimal('max_discount', 10, 2)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('offers');
    }
}; 