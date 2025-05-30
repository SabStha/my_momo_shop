<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order()
    {
        $customer = Customer::factory()->create();
        $product = Product::factory()->create();

        $response = $this->post('/checkout', [
            'customer_id' => $customer->id,
            'products' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2
                ]
            ]
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id
        ]);
    }

    public function test_can_view_order_details()
    {
        $order = Order::factory()->create();

        $response = $this->get("/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertSee($order->id);
    }
} 