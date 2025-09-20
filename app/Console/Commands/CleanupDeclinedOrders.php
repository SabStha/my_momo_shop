<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class CleanupDeclinedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cleanup-declined {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove declined orders older than 3 months from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        // Calculate the cutoff date (3 months ago)
        $cutoffDate = Carbon::now()->subMonths(3);
        
        $this->info("Looking for declined orders older than {$cutoffDate->format('Y-m-d H:i:s')}...");
        
        // Find declined orders older than 3 months
        $declinedOrders = Order::where('status', 'declined')
            ->where('created_at', '<', $cutoffDate)
            ->get();
        
        if ($declinedOrders->isEmpty()) {
            $this->info('No declined orders found older than 3 months.');
            return 0;
        }
        
        $this->info("Found {$declinedOrders->count()} declined orders to clean up:");
        
        // Display the orders that would be deleted
        $headers = ['ID', 'Order Number', 'Customer', 'Total', 'Created At'];
        $rows = [];
        
        foreach ($declinedOrders as $order) {
            $rows[] = [
                $order->id,
                $order->order_number ?? 'N/A',
                $order->customer_name ?? 'N/A',
                'Rs. ' . number_format($order->total_amount ?? 0, 2),
                $order->created_at->format('Y-m-d H:i:s')
            ];
        }
        
        $this->table($headers, $rows);
        
        if ($isDryRun) {
            $this->info('DRY RUN: No orders were actually deleted.');
            return 0;
        }
        
        // Confirm deletion
        if (!$this->confirm("Are you sure you want to delete these {$declinedOrders->count()} declined orders?")) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        // Delete the orders
        $deletedCount = 0;
        foreach ($declinedOrders as $order) {
            try {
                // Also delete related order items
                $order->items()->delete();
                
                // Delete the order
                $order->delete();
                $deletedCount++;
                
                $this->line("Deleted order #{$order->id} ({$order->order_number})");
            } catch (\Exception $e) {
                $this->error("Failed to delete order #{$order->id}: {$e->getMessage()}");
            }
        }
        
        $this->info("Successfully deleted {$deletedCount} declined orders and their related data.");
        
        // Log the cleanup
        \Log::info('Declined orders cleanup completed', [
            'deleted_count' => $deletedCount,
            'cutoff_date' => $cutoffDate->format('Y-m-d H:i:s'),
            'executed_by' => 'scheduled_command'
        ]);
        
        return 0;
    }
}
