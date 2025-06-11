<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\InventoryOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Get all orders with order_date as string
        $orders = DB::table('inventory_orders')
            ->whereNotNull('order_date')
            ->get();
        
        foreach ($orders as $order) {
            // Try to parse the date string
            try {
                $date = Carbon::parse($order->order_date);
                if ($date->format('Y-m-d') !== $order->order_date) {
                    DB::table('inventory_orders')
                        ->where('id', $order->id)
                        ->update(['order_date' => $date->format('Y-m-d')]);
                }
            } catch (\Exception $e) {
                // If parsing fails, set to current date
                DB::table('inventory_orders')
                    ->where('id', $order->id)
                    ->update(['order_date' => now()->format('Y-m-d')]);
            }
        }

        // Fix expected_delivery_date if needed
        $orders = DB::table('inventory_orders')
            ->whereNotNull('expected_delivery_date')
            ->get();
        
        foreach ($orders as $order) {
            try {
                $date = Carbon::parse($order->expected_delivery_date);
                if ($date->format('Y-m-d') !== $order->expected_delivery_date) {
                    DB::table('inventory_orders')
                        ->where('id', $order->id)
                        ->update(['expected_delivery_date' => $date->format('Y-m-d')]);
                }
            } catch (\Exception $e) {
                DB::table('inventory_orders')
                    ->where('id', $order->id)
                    ->update(['expected_delivery_date' => null]);
            }
        }

        // Fix actual_delivery_date if needed
        $orders = DB::table('inventory_orders')
            ->whereNotNull('actual_delivery_date')
            ->get();
        
        foreach ($orders as $order) {
            try {
                $date = Carbon::parse($order->actual_delivery_date);
                if ($date->format('Y-m-d') !== $order->actual_delivery_date) {
                    DB::table('inventory_orders')
                        ->where('id', $order->id)
                        ->update(['actual_delivery_date' => $date->format('Y-m-d')]);
                }
            } catch (\Exception $e) {
                DB::table('inventory_orders')
                    ->where('id', $order->id)
                    ->update(['actual_delivery_date' => null]);
            }
        }
    }

    public function down()
    {
        // No need to revert this migration
    }
}; 