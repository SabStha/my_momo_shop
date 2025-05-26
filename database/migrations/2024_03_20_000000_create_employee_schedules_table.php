<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('work_date');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Prevent overlapping shifts for the same employee
            $table->unique(['employee_id', 'work_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_schedules');
    }
}; 