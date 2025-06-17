<?php

namespace App\Http\Livewire\Customer;

use Livewire\Component;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;

class Payment extends Component
{
    public $order;
    public $selectedMethod;
    public $amount;
    public $paymentMethods;

    protected $rules = [
        'selectedMethod' => 'required|exists:payment_methods,code',
        'amount' => 'required|numeric|min:0'
    ];

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->amount = $order->total_amount;
        $this->paymentMethods = PaymentMethod::active()->ordered()->get();
        
        // Set default payment method if available
        if ($this->paymentMethods->isNotEmpty()) {
            $this->selectedMethod = $this->paymentMethods->first()->code;
        }
    }

    public function processPayment()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Check if payment already exists
            if ($this->order->payments()->exists()) {
                throw new \Exception('Payment already exists for this order.');
            }

            // Create payment record
            $payment = new Payment([
                'amount' => $this->amount,
                'payment_method_code' => $this->selectedMethod,
                'status' => 'pending',
                'order_id' => $this->order->id
            ]);

            $payment->save();

            // Process payment based on method
            $method = PaymentMethod::where('code', $this->selectedMethod)->first();
            
            switch ($method->code) {
                case 'cash':
                    $this->processCashPayment($payment);
                    break;
                case 'card':
                    $this->processCardPayment($payment);
                    break;
                case 'wallet':
                    $this->processWalletPayment($payment);
                    break;
                case 'qr':
                    $this->processQRPayment($payment);
                    break;
                default:
                    throw new \Exception('Unsupported payment method.');
            }

            DB::commit();

            // Emit success event
            $this->emit('payment-success', [
                'redirectUrl' => route('customer.orders.show', $this->order)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Emit error event
            $this->emit('payment-error', [
                'message' => $e->getMessage()
            ]);
        }
    }

    protected function processCashPayment($payment)
    {
        // For cash payments, we just need to mark it as completed
        $payment->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Update order status
        $this->order->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);
    }

    protected function processCardPayment($payment)
    {
        // Implement card payment processing logic
        // This would typically involve a payment gateway integration
        throw new \Exception('Card payment processing not implemented yet.');
    }

    protected function processWalletPayment($payment)
    {
        // Implement wallet payment processing logic
        throw new \Exception('Wallet payment processing not implemented yet.');
    }

    protected function processQRPayment($payment)
    {
        // Implement QR payment processing logic
        throw new \Exception('QR payment processing not implemented yet.');
    }

    public function render()
    {
        return view('customer.payment.index');
    }
} 