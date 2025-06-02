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
        // Get all orders with ordered_at as string
        $orders = DB::table('inventory_orders')
            ->whereNotNull('ordered_at')
            ->whereRaw('ordered_at NOT REGEXP "^[0-9]{4}-[0-9]{2}-[0-9]{2}"')
            ->get();
        
        foreach ($orders as $order) {
            // Try to parse the date string
            try {
                $date = Carbon::parse($order->ordered_at);
                DB::table('inventory_orders')
                    ->where('id', $order->id)
                    ->update(['ordered_at' => $date]);
            } catch (\Exception $e) {
                // If parsing fails, set to current time
                DB::table('inventory_orders')
                    ->where('id', $order->id)
                    ->update(['ordered_at' => now()]);
            }
        }
    }

    public function down()
    {
        // No need to revert this migration
    }
}; 