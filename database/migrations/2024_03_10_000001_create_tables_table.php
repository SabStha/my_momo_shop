<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create tables table
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('number')->unique();
            $table->integer('capacity')->default(4);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_occupied')->default(false);
            $table->string('status')->default('available'); // available, occupied, reserved
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('branch_id');
            $table->index('name');
            $table->index('number');
            $table->index('status');
            $table->index('is_active');
        });

        // Create schedules table
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('day');
            $table->time('open_time');
            $table->time('close_time');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Add indexes
            $table->index('branch_id');
            $table->index('day');
        });
    }

    public function down()
    {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('tables');
    }
}; 