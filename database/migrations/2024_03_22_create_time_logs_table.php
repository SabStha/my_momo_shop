<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('set null');
            $table->date('date');
            $table->dateTime('clock_in');
            $table->dateTime('clock_out')->nullable();
            $table->string('status')->default('active'); // active, break, completed
            $table->dateTime('break_start')->nullable();
            $table->dateTime('break_end')->nullable();
            $table->integer('break_duration')->default(0); // in minutes
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for frequently queried columns
            $table->index('employee_id');
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('date');
            $table->index('clock_in');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_logs');
    }
}; 