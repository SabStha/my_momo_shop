<?php

namespace Tests\Unit\Policies;

use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private OrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->policy = new OrderPolicy();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'cashier']);
        Role::create(['name' => 'employee']);
        Role::create(['name' => 'customer']);
    }

    /** @test */
    public function admin_can_view_any_orders()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $this->assertTrue($this->policy->viewAny($admin));
    }

    /** @test */
    public function cashier_can_view_any_orders()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        
        $this->assertTrue($this->policy->viewAny($cashier));
    }

    /** @test */
    public function employee_can_view_any_orders()
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        
        $this->assertTrue($this->policy->viewAny($employee));
    }

    /** @test */
    public function customer_cannot_view_any_orders()
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');
        
        $this->assertFalse($this->policy->viewAny($customer));
    }

    /** @test */
    public function admin_can_view_any_specific_order()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $order = Order::factory()->create();
        
        $this->assertTrue($this->policy->view($admin, $order));
    }

    /** @test */
    public function user_can_view_their_own_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        
        $this->assertTrue($this->policy->view($user, $order));
    }

    /** @test */
    public function user_cannot_view_others_orders()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $order = Order::factory()->create(['user_id' => $user2->id]);
        
        $this->assertFalse($this->policy->view($user1, $order));
    }

    /** @test */
    public function employee_can_view_orders_they_created()
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        
        $order = Order::factory()->create(['created_by' => $employee->id]);
        
        $this->assertTrue($this->policy->view($employee, $order));
    }

    /** @test */
    public function employee_cannot_view_orders_created_by_others()
    {
        $employee1 = User::factory()->create();
        $employee1->assignRole('employee');
        
        $employee2 = User::factory()->create();
        $employee2->assignRole('employee');
        
        $order = Order::factory()->create(['created_by' => $employee2->id]);
        
        $this->assertFalse($this->policy->view($employee1, $order));
    }

    /** @test */
    public function admin_can_create_orders()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $this->assertTrue($this->policy->create($admin));
    }

    /** @test */
    public function cashier_can_create_orders()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        
        $this->assertTrue($this->policy->create($cashier));
    }

    /** @test */
    public function employee_can_create_orders()
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        
        $this->assertTrue($this->policy->create($employee));
    }

    /** @test */
    public function customer_cannot_create_orders()
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');
        
        $this->assertFalse($this->policy->create($customer));
    }

    /** @test */
    public function admin_can_update_any_order()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $order = Order::factory()->create();
        
        $this->assertTrue($this->policy->update($admin, $order));
    }

    /** @test */
    public function cashier_can_update_any_order()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        
        $order = Order::factory()->create();
        
        $this->assertTrue($this->policy->update($cashier, $order));
    }

    /** @test */
    public function employee_can_update_pending_orders_they_created()
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        
        $order = Order::factory()->create([
            'created_by' => $employee->id,
            'status' => 'pending'
        ]);
        
        $this->assertTrue($this->policy->update($employee, $order));
    }

    /** @test */
    public function employee_cannot_update_completed_orders_they_created()
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        
        $order = Order::factory()->create([
            'created_by' => $employee->id,
            'status' => 'completed'
        ]);
        
        $this->assertFalse($this->policy->update($employee, $order));
    }

    /** @test */
    public function only_admin_can_delete_orders()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        
        $order = Order::factory()->create();
        
        $this->assertTrue($this->policy->delete($admin, $order));
        $this->assertFalse($this->policy->delete($cashier, $order));
    }

    /** @test */
    public function cashier_can_delete_pending_orders()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        $completedOrder = Order::factory()->create(['status' => 'completed']);
        
        $this->assertTrue($this->policy->delete($cashier, $pendingOrder));
        $this->assertFalse($this->policy->delete($cashier, $completedOrder));
    }

    /** @test */
    public function only_admin_and_cashier_can_process_payments()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        
        $order = Order::factory()->create();
        
        $this->assertTrue($this->policy->processPayment($admin, $order));
        $this->assertTrue($this->policy->processPayment($cashier, $order));
        $this->assertFalse($this->policy->processPayment($employee, $order));
    }
}