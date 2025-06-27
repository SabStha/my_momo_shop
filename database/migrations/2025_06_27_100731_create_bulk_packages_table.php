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
        Schema::create('bulk_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('emoji');
            $table->text('description');
            $table->enum('type', ['cooked', 'frozen']);
            $table->string('package_key')->unique(); // party, family, office, etc.
            $table->json('items'); // JSON array of items with prices
            $table->decimal('total_price', 8, 2);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_packages');
    }
};
