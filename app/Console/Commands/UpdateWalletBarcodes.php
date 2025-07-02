<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Wallet;

class UpdateWalletBarcodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:update-barcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing wallets with barcodes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $wallets = Wallet::whereNull('barcode')->get();
        
        if ($wallets->isEmpty()) {
            $this->info('All wallets already have barcodes!');
            return;
        }

        $this->info("Updating {$wallets->count()} wallets with barcodes...");
        
        $bar = $this->output->createProgressBar($wallets->count());
        
        foreach ($wallets as $wallet) {
            $wallet->barcode = Wallet::generateBarcode();
            $wallet->save();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('All wallets updated successfully!');
    }
}
