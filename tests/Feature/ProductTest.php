<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_products()
    {
        $products = Product::factory()->count(3)->create();

        $response = $this->get('/products');

        $response->assertStatus(200);
        foreach ($products as $product) {
            $response->assertSee($product->name);
        }
    }

    public function test_can_view_single_product()
    {
        $product = Product::factory()->create();

        $response = $this->get("/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->description);
    }

    public function test_admin_can_create_product()
    {
        $this->actingAs($this->createAdminUser());

        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 9.99,
            'category' => 'Test Category'
        ];

        $response = $this->post('/admin/products', $productData);

        $response->assertStatus(302);
        $this->assertDatabaseHas('products', $productData);
    }
} 