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
        Schema::table('bulk_packages', function (Blueprint $table) {
            $table->string('feeds_people')->nullable(); // e.g., "8–10 people"
            $table->string('savings_description')->nullable(); // e.g., "Save Rs. 250+ vs buying individually"
            $table->decimal('original_price', 8, 2)->nullable(); // Original price for comparison
            $table->string('delivery_note')->nullable(); // e.g., "Order before 2PM for same-day delivery"
            $table->string('deal_title')->nullable(); // e.g., "Party Pack Deal"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulk_packages', function (Blueprint $table) {
            $table->dropColumn(['feeds_people', 'savings_description', 'original_price', 'delivery_note', 'deal_title']);
        });
    }
};