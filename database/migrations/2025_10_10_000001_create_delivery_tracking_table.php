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
        Schema::create('delivery_trackings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('driver_id');
            $table->string('status'); // accepted, location_update, delivered
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('photo_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['order_id', 'created_at']);
            $table->index('driver_id');
        });
        
        // Add delivery-related columns to orders table if they don't exist
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'assigned_driver_id')) {
                $table->unsignedBigInteger('assigned_driver_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('orders', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'preparing_started_at')) {
                $table->timestamp('preparing_started_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'ready_at')) {
                $table->timestamp('ready_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'out_for_delivery_at')) {
                $table->timestamp('out_for_delivery_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'delivery_photo')) {
                $table->string('delivery_photo')->nullable();
            }
            if (!Schema::hasColumn('orders', 'delivery_notes')) {
                $table->text('delivery_notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_trackings');
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_driver_id',
                'confirmed_at',
                'preparing_started_at',
                'ready_at',
                'out_for_delivery_at',
                'delivery_photo',
                'delivery_notes'
            ]);
        });
    }
};

