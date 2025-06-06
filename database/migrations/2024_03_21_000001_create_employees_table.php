<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('position');
            $table->decimal('salary', 10, 2);
            $table->date('hire_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}; 