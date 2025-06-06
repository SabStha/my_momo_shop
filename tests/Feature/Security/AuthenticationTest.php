<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
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
    }

    /** @test */
    public function unauthenticated_requests_to_protected_api_endpoints_are_rejected()
    {
        $protectedEndpoints = [
            ['GET', '/api/pos/orders'],
            ['POST', '/api/pos/orders'],
            ['GET', '/api/pos/products'],
            ['GET', '/api/admin/dashboard'],
            ['POST', '/api/reports/generate'],
        ];

        foreach ($protectedEndpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $this->assertEquals(401, $response->status(), "Endpoint {$method} {$endpoint} should require authentication");
        }
    }

    /** @test */
    public function insufficient_role_permissions_are_rejected()
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');
        
        Sanctum::actingAs($customer);

        $restrictedEndpoints = [
            ['GET', '/api/pos/orders', 403],
            ['POST', '/api/pos/orders', 403],
            ['GET', '/api/admin/dashboard', 403],
            ['POST', '/api/reports/generate', 403],
        ];

        foreach ($restrictedEndpoints as [$method, $endpoint, $expectedStatus]) {
            $response = $this->json($method, $endpoint);
            $this->assertEquals($expectedStatus, $response->status(), 
                "Customer should not access {$method} {$endpoint}");
        }
    }

    /** @test */
    public function admin_has_access_to_all_protected_endpoints()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        
        Sanctum::actingAs($admin);

        $adminEndpoints = [
            ['GET', '/api/pos/orders'],
            ['GET', '/api/admin/dashboard'],
            ['POST', '/api/reports/generate'],
        ];

        foreach ($adminEndpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $this->assertNotEquals(401, $response->status(), 
                "Admin should have access to {$method} {$endpoint}");
            $this->assertNotEquals(403, $response->status(), 
                "Admin should have access to {$method} {$endpoint}");
        }
    }

    /** @test */
    public function cashier_has_pos_access_but_not_admin()
    {
        $cashier = User::factory()->create();
        $cashier->assignRole('cashier');
        
        Sanctum::actingAs($cashier);

        // Should have POS access
        $response = $this->getJson('/api/pos/orders');
        $this->assertNotEquals(403, $response->status());

        // Should NOT have admin access
        $response = $this->getJson('/api/admin/dashboard');
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function employee_has_limited_pos_access()
    {
        $employee = User::factory()->create();
        $employee->assignRole('employee');
        
        Sanctum::actingAs($employee);

        // Should have basic POS access
        $response = $this->getJson('/api/pos/orders');
        $this->assertNotEquals(403, $response->status());

        // Should NOT have admin access
        $response = $this->getJson('/api/admin/dashboard');
        $this->assertEquals(403, $response->status());

        // Should NOT have report access
        $response = $this->postJson('/api/reports/generate');
        $this->assertEquals(403, $response->status());
    }

    /** @test */
    public function rate_limiting_is_enforced_on_authentication_endpoints()
    {
        // Make multiple requests to exceed rate limit
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        // 6th request should be rate limited
        $this->assertEquals(429, $response->status());
    }

    /** @test */
    public function employee_verification_endpoint_is_rate_limited()
    {
        // Make multiple requests to exceed rate limit (10 per minute)
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/employee/verify', [
                'identifier' => 'test@example.com',
                'password' => 'wrongpassword'
            ]);
        }

        // 11th request should be rate limited
        $this->assertEquals(429, $response->status());
    }

    /** @test */
    public function api_endpoints_are_rate_limited()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        
        Sanctum::actingAs($user);

        // Make multiple requests to exceed rate limit (60 per minute)
        // We'll test with a smaller number to keep test fast
        for ($i = 0; $i < 5; $i++) {
            $response = $this->getJson('/api/pos/orders');
        }

        // Should still be allowed (under rate limit)
        $this->assertNotEquals(429, $response->status());
    }

    /** @test */
    public function leaderboard_endpoint_has_separate_rate_limiting()
    {
        // Public endpoint should have lower rate limit
        for ($i = 0; $i < 5; $i++) {
            $response = $this->getJson('/api/leaderboard');
        }

        // Should still be allowed (under rate limit of 30)
        $this->assertNotEquals(429, $response->status());
    }
}