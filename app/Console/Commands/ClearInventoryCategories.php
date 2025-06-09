<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearInventoryCategories extends Command
{
    protected $signature = 'inventory:clear-categories';
    protected $description = 'Clear all inventory categories';

    public function handle()
    {
        if ($this->confirm('This will delete all inventory categories. Are you sure?')) {
            DB::table('inventory_categories')->truncate();
            $this->info('All inventory categories have been cleared.');
        }
    }
} 