<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BranchInventory;

class SetupMainBranch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:setup-main';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the main branch for inventory management';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if a main branch already exists
        $existingMain = BranchInventory::where('is_main', true)->first();
        if ($existingMain) {
            $this->info('Main branch already exists: ' . $existingMain->name);
            return;
        }

        // Create the main branch
        $mainBranch = BranchInventory::create([
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'address' => 'Main Office',
            'contact' => 'Main Office Contact',
            'is_active' => true,
            'is_main' => true,
        ]);

        $this->info('Main branch created successfully: ' . $mainBranch->name);
    }
}
