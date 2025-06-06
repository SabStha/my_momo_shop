<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_cannot_mass_assign_financial_fields()
    {
        $user = User::factory()->create();
        
        $order = Order::create([
            'order_number' => 'TEST-001',
            'type' => 'takeaway',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
            'user_id' => $user->id,
            // These should be blocked by mass assignment protection
            'total_amount' => 999.99,
            'tax_amount' => 99.99,
            'grand_total' => 1099.98,
            'amount_received' => 1100.00,
            'change' => 0.02,
        ]);

        // Financial fields should not have been mass assigned
        $this->assertNull($order->total_amount);
        $this->assertNull($order->tax_amount);
        $this->assertNull($order->grand_total);
        $this->assertNull($order->amount_received);
        $this->assertNull($order->change);
    }

    /** @test */
    public function order_financial_fields_can_be_set_individually()
    {
        $order = Order::factory()->create();
        
        // Should be able to set financial fields directly
        $order->total_amount = 50.00;
        $order->tax_amount = 6.50;
        $order->grand_total = 56.50;
        $order->save();
        
        $this->assertEquals(50.00, $order->fresh()->total_amount);
        $this->assertEquals(6.50, $order->fresh()->tax_amount);
        $this->assertEquals(56.50, $order->fresh()->grand_total);
    }

    /** @test */
    public function order_casts_financial_fields_to_decimal()
    {
        $order = Order::factory()->create([
            'total_amount' => '50.123456',
            'tax_amount' => '6.789012'
        ]);
        
        // Should be cast to 2 decimal places
        $this->assertEquals('50.12', $order->total_amount);
        $this->assertEquals('6.79', $order->tax_amount);
    }

    /** @test */
    public function order_has_correct_relationships()
    {
        $user = User::factory()->create();
        $creator = User::factory()->create();
        $payer = User::factory()->create();
        
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'created_by' => $creator->id,
            'paid_by' => $payer->id
        ]);
        
        $this->assertEquals($user->id, $order->user->id);
        $this->assertEquals($creator->id, $order->createdBy->id);
        $this->assertEquals($payer->id, $order->paidBy->id);
    }

    /** @test */
    public function order_status_badge_returns_correct_class()
    {
        $completedOrder = Order::factory()->create(['status' => 'completed']);
        $cancelledOrder = Order::factory()->create(['status' => 'cancelled']);
        $pendingOrder = Order::factory()->create(['status' => 'pending']);
        
        $this->assertEquals('success', $completedOrder->status_badge);
        $this->assertEquals('danger', $cancelledOrder->status_badge);
        $this->assertEquals('warning', $pendingOrder->status_badge);
    }
}