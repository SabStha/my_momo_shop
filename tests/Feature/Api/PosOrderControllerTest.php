<?php

namespace Tests\Feature\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PosOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'cashier']);
        Role::create(['name' => 'employee']);
        Role::create(['name' => 'customer']);
        
        // Create test data
        $this->createTestData();
    }

    private function createTestData()
    {
        // Create users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->cashier = User::factory()->create();
        $this->cashier->assignRole('cashier');
        
        $this->employee = User::factory()->create();
        $this->employee->assignRole('employee');
        
        $this->customer = User::factory()->create();
        $this->customer->assignRole('customer');
        
        // Create products
        $this->product1 = Product::factory()->create([
            'name' => 'Test Product 1',
            'price' => 10.00,
            'active' => true
        ]);
        
        $this->product2 = Product::factory()->create([
            'name' => 'Test Product 2',
            'price' => 15.00,
            'active' => true
        ]);
        
        $this->inactiveProduct = Product::factory()->create([
            'name' => 'Inactive Product',
            'price' => 20.00,
            'active' => false
        ]);
        
        // Create table
        $this->table = Table::factory()->create([
            'number' => 1,
            'name' => 'Table 1',
            'status' => 'available'
        ]);
    }

    /** @test */
    public function unauthenticated_users_cannot_access_pos_endpoints()
    {
        $response = $this->getJson('/api/pos/orders');
        $response->assertStatus(401);
        
        $response = $this->postJson('/api/pos/orders', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function customers_cannot_access_pos_endpoints()
    {
        Sanctum::actingAs($this->customer);
        
        $response = $this->getJson('/api/pos/orders');
        $response->assertStatus(403);
        
        $response = $this->postJson('/api/pos/orders', []);
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_order_successfully()
    {
        Sanctum::actingAs($this->admin);
        
        $orderData = [
            'type' => 'dine-in',
            'table_id' => $this->table->id,
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 2
                ],
                [
                    'product_id' => $this->product2->id,
                    'quantity' => 1
                ]
            ],
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com'
        ];
        
        $response = $this->postJson('/api/pos/orders', $orderData);
        
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'order' => [
                         'id',
                         'order_number',
                         'type',
                         'status',
                         'payment_status',
                         'total_amount',
                         'tax_amount',
                         'grand_total',
                         'items'
                     ],
                     'message'
                 ]);
        
        // Assert order was created correctly
        $this->assertDatabaseHas('orders', [
            'type' => 'dine-in',
            'table_id' => $this->table->id,
            'status' => 'pending',
            'total_amount' => 35.00, // (10*2) + (15*1)
            'created_by' => $this->admin->id
        ]);
    }

    /** @test */
    public function cannot_create_order_with_inactive_product()
    {
        Sanctum::actingAs($this->admin);
        
        $orderData = [
            'type' => 'takeaway',
            'items' => [
                [
                    'product_id' => $this->inactiveProduct->id,
                    'quantity' => 1
                ]
            ]
        ];
        
        $response = $this->postJson('/api/pos/orders', $orderData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['items.0.product_id']);
    }

    /** @test */
    public function dine_in_order_requires_table_id()
    {
        Sanctum::actingAs($this->admin);
        
        $orderData = [
            'type' => 'dine-in',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 1
                ]
            ]
        ];
        
        $response = $this->postJson('/api/pos/orders', $orderData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['table_id']);
    }

    /** @test */
    public function order_validation_prevents_excessive_quantities()
    {
        Sanctum::actingAs($this->admin);
        
        $orderData = [
            'type' => 'takeaway',
            'items' => [
                [
                    'product_id' => $this->product1->id,
                    'quantity' => 100 // Exceeds max of 99
                ]
            ]
        ];
        
        $response = $this->postJson('/api/pos/orders', $orderData);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function employee_can_only_see_their_own_orders()
    {
        Sanctum::actingAs($this->employee);
        
        // Create orders by different users
        $employeeOrder = Order::factory()->create(['created_by' => $this->employee->id]);
        $adminOrder = Order::factory()->create(['created_by' => $this->admin->id]);
        
        $response = $this->getJson('/api/pos/orders');
        
        $response->assertStatus(200);
        $orders = $response->json('orders');
        
        // Employee should only see their own order
        $this->assertCount(1, $orders);
        $this->assertEquals($employeeOrder->id, $orders[0]['id']);
    }

    /** @test */
    public function admin_can_see_all_orders()
    {
        Sanctum::actingAs($this->admin);
        
        // Create orders by different users
        $employeeOrder = Order::factory()->create(['created_by' => $this->employee->id]);
        $adminOrder = Order::factory()->create(['created_by' => $this->admin->id]);
        
        $response = $this->getJson('/api/pos/orders');
        
        $response->assertStatus(200);
        $orders = $response->json('orders');
        
        // Admin should see all orders
        $this->assertCount(2, $orders);
    }

    /** @test */
    public function cannot_update_order_without_permission()
    {
        Sanctum::actingAs($this->employee);
        
        // Create order by another user
        $order = Order::factory()->create([
            'created_by' => $this->admin->id,
            'status' => 'pending'
        ]);
        
        $response = $this->putJson("/api/pos/orders/{$order->id}/status", [
            'status' => 'preparing'
        ]);
        
        $response->assertStatus(403);
    }

    /** @test */
    public function employee_can_update_their_own_pending_orders()
    {
        Sanctum::actingAs($this->employee);
        
        $order = Order::factory()->create([
            'created_by' => $this->employee->id,
            'status' => 'pending'
        ]);
        
        $response = $this->putJson("/api/pos/orders/{$order->id}/status", [
            'status' => 'preparing'
        ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'preparing'
        ]);
    }

    /** @test */
    public function cannot_transition_to_invalid_status()
    {
        Sanctum::actingAs($this->admin);
        
        $order = Order::factory()->create([
            'status' => 'completed'
        ]);
        
        $response = $this->putJson("/api/pos/orders/{$order->id}/status", [
            'status' => 'pending'
        ]);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function only_admin_can_delete_orders()
    {
        Sanctum::actingAs($this->cashier);
        
        $order = Order::factory()->create(['status' => 'completed']);
        
        $response = $this->deleteJson("/api/pos/orders/{$order->id}");
        
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_orders()
    {
        Sanctum::actingAs($this->admin);
        
        $order = Order::factory()->create();
        
        $response = $this->deleteJson("/api/pos/orders/{$order->id}");
        
        $response->assertStatus(200);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    /** @test */
    public function financial_data_not_exposed_to_unauthorized_users()
    {
        Sanctum::actingAs($this->employee);
        
        $order = Order::factory()->create([
            'created_by' => $this->employee->id,
            'amount_received' => 50.00,
            'change' => 15.00
        ]);
        
        $response = $this->getJson("/api/pos/orders/{$order->id}");
        
        $response->assertStatus(200);
        $orderData = $response->json('order');
        
        // Financial details should not be exposed to employees
        $this->assertArrayNotHasKey('amount_received', $orderData);
        $this->assertArrayNotHasKey('change', $orderData);
    }

    /** @test */
    public function financial_data_exposed_to_authorized_users()
    {
        Sanctum::actingAs($this->admin);
        
        $order = Order::factory()->create([
            'amount_received' => 50.00,
            'change' => 15.00
        ]);
        
        $response = $this->getJson("/api/pos/orders/{$order->id}");
        
        $response->assertStatus(200);
        $orderData = $response->json('order');
        
        // Financial details should be exposed to admin
        $this->assertArrayHasKey('amount_received', $orderData);
        $this->assertArrayHasKey('change', $orderData);
    }
}