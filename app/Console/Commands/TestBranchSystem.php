<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\Order;
use App\Models\Employee;
use App\Models\Table;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class TestBranchSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'branch:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the branch system functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Branch System...');

        // 1. Test Branch Creation
        $this->info("\n1. Testing Branch Creation...");
        $mainBranch = BranchInventory::where('is_main', true)->first();
        if (!$mainBranch) {
            $this->error('Main branch not found!');
            return 1;
        }
        $this->info('✓ Main branch exists');

        // 2. Test Data Isolation
        $this->info("\n2. Testing Data Isolation...");
        
        // Create test branches
        $branch1 = BranchInventory::create([
            'name' => 'Test Branch 1',
            'code' => 'TB1',
            'address' => 'Test Address 1',
            'contact' => '1234567890',
            'is_active' => true
        ]);

        $branch2 = BranchInventory::create([
            'name' => 'Test Branch 2',
            'code' => 'TB2',
            'address' => 'Test Address 2',
            'contact' => '0987654321',
            'is_active' => true
        ]);

        // Create test data for each branch
        $this->createTestData($branch1);
        $this->createTestData($branch2);

        // Verify data isolation
        $this->verifyDataIsolation($branch1, $branch2);

        // 3. Test Shared Features
        $this->info("\n3. Testing Shared Features...");
        $this->testSharedFeatures();

        // Clean up test data
        $this->cleanupTestData($branch1, $branch2);

        $this->info("\nBranch System Tests Completed Successfully!");
        return 0;
    }

    private function createTestData($branch)
    {
        // Create test products
        Product::create([
            'name' => "Test Product for {$branch->name}",
            'description' => 'Test Description',
            'price' => 100,
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        // Create test orders
        Order::create([
            'user_id' => 1,
            'status' => 'pending',
            'total_amount' => 100,
            'branch_id' => $branch->id
        ]);

        // Create test employees
        Employee::create([
            'user_id' => 1,
            'position' => 'Test Position',
            'hire_date' => now(),
            'salary' => 1000,
            'branch_id' => $branch->id,
            'status' => 'active'
        ]);

        // Create test tables
        Table::create([
            'name' => "Test Table for {$branch->name}",
            'capacity' => 4,
            'status' => 'available',
            'branch_id' => $branch->id,
            'is_active' => true
        ]);

        // Create test wallets
        Wallet::create([
            'user_id' => 1,
            'balance' => 1000,
            'branch_id' => $branch->id,
            'is_active' => true
        ]);
    }

    private function verifyDataIsolation($branch1, $branch2)
    {
        // Verify products isolation
        $branch1Products = Product::where('branch_id', $branch1->id)->count();
        $branch2Products = Product::where('branch_id', $branch2->id)->count();
        $this->info("Branch 1 Products: {$branch1Products}");
        $this->info("Branch 2 Products: {$branch2Products}");
        $this->assert($branch1Products === $branch2Products, 'Products isolation');

        // Verify orders isolation
        $branch1Orders = Order::where('branch_id', $branch1->id)->count();
        $branch2Orders = Order::where('branch_id', $branch2->id)->count();
        $this->info("Branch 1 Orders: {$branch1Orders}");
        $this->info("Branch 2 Orders: {$branch2Orders}");
        $this->assert($branch1Orders === $branch2Orders, 'Orders isolation');

        // Verify employees isolation
        $branch1Employees = Employee::where('branch_id', $branch1->id)->count();
        $branch2Employees = Employee::where('branch_id', $branch2->id)->count();
        $this->info("Branch 1 Employees: {$branch1Employees}");
        $this->info("Branch 2 Employees: {$branch2Employees}");
        $this->assert($branch1Employees === $branch2Employees, 'Employees isolation');
    }

    private function testSharedFeatures()
    {
        // Test shared menu items
        $sharedProducts = Product::whereNull('branch_id')->count();
        $this->info("Shared Products: {$sharedProducts}");
        $this->assert($sharedProducts > 0, 'Shared products exist');

        // Test shared wallet transactions
        $sharedWallets = Wallet::whereNull('branch_id')->count();
        $this->info("Shared Wallets: {$sharedWallets}");
        $this->assert($sharedWallets > 0, 'Shared wallets exist');
    }

    private function cleanupTestData($branch1, $branch2)
    {
        DB::transaction(function () use ($branch1, $branch2) {
            // Delete test data
            Product::where('branch_id', $branch1->id)->delete();
            Product::where('branch_id', $branch2->id)->delete();
            
            Order::where('branch_id', $branch1->id)->delete();
            Order::where('branch_id', $branch2->id)->delete();
            
            Employee::where('branch_id', $branch1->id)->delete();
            Employee::where('branch_id', $branch2->id)->delete();
            
            Table::where('branch_id', $branch1->id)->delete();
            Table::where('branch_id', $branch2->id)->delete();
            
            Wallet::where('branch_id', $branch1->id)->delete();
            Wallet::where('branch_id', $branch2->id)->delete();

            // Delete test branches
            $branch1->delete();
            $branch2->delete();
        });
    }

    private function assert($condition, $message)
    {
        if ($condition) {
            $this->info("✓ {$message} verified");
        } else {
            $this->error("✗ {$message} failed");
        }
    }
}
