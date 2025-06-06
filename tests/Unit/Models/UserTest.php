<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'cashier']);
        Role::create(['name' => 'employee']);
    }

    /** @test */
    public function user_cannot_mass_assign_privileged_fields()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            // These should be blocked by mass assignment protection
            'is_admin' => true,
            'points' => 1000,
            'role' => 'admin'
        ]);

        // Should not have admin privileges or points
        $this->assertFalse($user->is_admin ?? false);
        $this->assertNull($user->points);
        $this->assertNull($user->role);
    }

    /** @test */
    public function user_admin_check_works_correctly()
    {
        $user = User::factory()->create();
        
        // Initially not admin
        $this->assertFalse($user->isAdmin());
        
        // Assign admin role
        $user->assignRole('admin');
        
        // Now should be admin
        $this->assertTrue($user->isAdmin());
    }

    /** @test */
    public function user_cashier_check_works_correctly()
    {
        $user = User::factory()->create();
        
        // Initially not cashier
        $this->assertFalse($user->isCashier());
        
        // Assign cashier role
        $user->assignRole('cashier');
        
        // Now should be cashier
        $this->assertTrue($user->isCashier());
    }

    /** @test */
    public function user_employee_check_works_correctly()
    {
        $user = User::factory()->create();
        
        // Initially not employee
        $this->assertFalse($user->isEmployee());
        
        // Assign employee role
        $user->assignRole('employee');
        
        // Now should be employee
        $this->assertTrue($user->isEmployee());
    }

    /** @test */
    public function wallet_is_created_automatically_when_user_is_created()
    {
        $user = User::factory()->create();
        
        $this->assertNotNull($user->wallet);
        $this->assertEquals(0, $user->wallet->balance);
    }

    /** @test */
    public function user_hidden_attributes_are_not_serialized()
    {
        $user = User::factory()->create([
            'password' => 'secret_password'
        ]);
        
        $userArray = $user->toArray();
        
        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
    }
}