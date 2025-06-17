<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('churn_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->decimal('churn_probability', 5, 2);
            $table->json('risk_factors');
            $table->timestamp('last_updated');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('churn_predictions');
    }
}; 