<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tables')) {
            Schema::create('tables', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('status')->default('available');
                $table->integer('capacity')->default(4);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tables');
    }
}; 