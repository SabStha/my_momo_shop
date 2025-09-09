<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Payment\ESewaPaymentProcessor;
use App\Models\Payment;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ESewaPaymentProcessorTest extends TestCase
{
    use RefreshDatabase;

    protected $processor;
    protected $user;
    protected $order;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->processor = new ESewaPaymentProcessor();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test order
        $this->order = Order::create([
            'user_id' => $this->user->id,
            'total_amount' => 1000.00,
            'status' => 'pending',
            'branch_id' => 1, // Assuming branch exists
        ]);
        
        // Create test payment
        $this->payment = Payment::create([
            'order_id' => $this->order->id,
            'user_id' => $this->user->id,
            'amount' => 1000.00,
            'currency' => 'NPR',
            'status' => 'pending',
            'payment_method_id' => 1, // Assuming payment method exists
        ]);
    }

    /** @test */
    public function it_can_initialize_esewa_payment()
    {
        $result = $this->processor->initialize($this->payment);

        $this->assertTrue($result['success']);
        $this->assertEquals('eSewa payment initialized successfully', $result['message']);
        $this->assertArrayHasKey('payment_id', $result['data']);
        $this->assertArrayHasKey('transaction_id', $result['data']);
        $this->assertArrayHasKey('payment_url', $result['data']);
        $this->assertTrue($result['data']['redirect_required']);
    }

    /** @test */
    public function it_can_process_esewa_payment()
    {
        $result = $this->processor->process($this->payment);

        $this->assertTrue($result['success']);
        $this->assertEquals('Payment redirected to eSewa', $result['message']);
        $this->assertEquals('pending', $result['data']['status']);
        $this->assertTrue($result['data']['redirect_required']);
    }

    /** @test */
    public function it_can_cancel_esewa_payment()
    {
        $result = $this->processor->cancel($this->payment);

        $this->assertTrue($result['success']);
        $this->assertEquals('Payment cancelled successfully', $result['message']);
        $this->assertEquals('cancelled', $result['data']['status']);
    }

    /** @test */
    public function it_generates_valid_payment_url()
    {
        $result = $this->processor->initialize($this->payment);
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('https://esewa.com.np/epay/testtransac', $result['data']['payment_url']);
        $this->assertStringContainsString('amt=1000', $result['data']['payment_url']);
        $this->assertStringContainsString('pid=' . $this->payment->id, $result['data']['payment_url']);
    }

    /** @test */
    public function it_stores_metadata_correctly()
    {
        $result = $this->processor->initialize($this->payment);
        
        $this->payment->refresh();
        $metadata = $this->payment->metadata;
        
        $this->assertArrayHasKey('transaction_id', $metadata);
        $this->assertArrayHasKey('esewa_merchant_id', $metadata);
        $this->assertArrayHasKey('initialized_at', $metadata);
        $this->assertStringStartsWith('ESEWA_', $metadata['transaction_id']);
    }
}
