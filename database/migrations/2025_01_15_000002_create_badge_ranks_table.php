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
        Schema::create('badge_ranks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('badge_class_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Bronze, Silver, Gold
            $table->string('code'); // bronze, silver, gold
            $table->integer('level'); // 1, 2, 3 (for ordering)
            $table->text('description');
            $table->string('color'); // CSS color or color identifier
            $table->json('requirements')->nullable(); // JSON requirements for this rank
            $table->json('benefits')->nullable(); // JSON benefits for this rank
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['badge_class_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_ranks');
    }
}; 