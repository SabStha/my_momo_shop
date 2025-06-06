<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CheckDatabaseData extends Command
{
    protected $signature = 'db:check';
    protected $description = 'Check database data for orders and products';

    public function handle()
    {
        $this->info('Checking database data...');
        
        // Check database connection
        $this->info('Database: ' . DB::connection()->getDatabaseName());
        
        // Check orders
        $orderCount = Order::count();
        $this->info("Total Orders: {$orderCount}");
        
        // Check products
        $productCount = Product::count();
        $this->info("Total Products: {$productCount}");
        
        // Check revenue
        $totalRevenue = Order::sum('total_amount');
        $this->info("Total Revenue: {$totalRevenue}");
        
        // Show sample data
        $this->info("\nSample Order:");
        $sampleOrder = Order::with('user')->first();
        if ($sampleOrder) {
            $this->table(
                ['ID', 'User', 'Total', 'Status', 'Created At'],
                [[
                    $sampleOrder->id,
                    $sampleOrder->user ? $sampleOrder->user->name : 'N/A',
                    $sampleOrder->total_amount,
                    $sampleOrder->status,
                    $sampleOrder->created_at
                ]]
            );
        }
    }
} 