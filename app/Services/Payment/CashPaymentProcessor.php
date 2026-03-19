<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Models\CashDrawer;
use App\Models\CashDrawerSession;
use App\Services\Payment\Contracts\PaymentProcessorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashPaymentProcessor implements PaymentProcessorInterface
{
    public function initialize(Payment $payment): PaymentResponse
    {
        return PaymentResponse::success('pending', 'Cash payment initialized', [
            'payment_id' => $payment->id
        ]);
    }

    public function process(Payment $payment): PaymentResponse
    {
        try {
            DB::beginTransaction();

            $order = $payment->order;
            
            // Handle cash drawer logic
            $session = CashDrawerSession::where('branch_id', $payment->branch_id)
                ->whereNull('closed_at')
                ->first();

            if (!$session) {
                throw new \Exception('Please open a cash drawer session before processing cash payments.');
            }

            $cashDrawer = CashDrawer::where('branch_id', $payment->branch_id)
                ->whereDate('date', today())
                ->first();

            if (!$cashDrawer) {
                $cashDrawer = CashDrawer::create([
                    'branch_id' => $payment->branch_id,
                    'date' => today()->toDateString(),
                    'starting_amount' => 0,
                    'current_balance' => 0,
                    'total_cash' => 0,
                    'total_sales' => 0,
                    'status' => 'open'
                ]);
            }

            // Update cash drawer
            $cashDrawer->total_cash += $payment->amount;
            $cashDrawer->total_sales += $payment->amount;
            $cashDrawer->current_balance += $payment->amount;
            $cashDrawer->save();

            // Update session
            $session->current_balance += $payment->amount;
            $session->save();

            // Update payment
            $payment->status = 'completed';
            $payment->paid_at = now();
            $payment->completed_at = now();
            $payment->save();

            // Update order
            $order->update([
                'status' => 'completed',
                'payment_status' => 'paid'
            ]);

            DB::commit();

            return PaymentResponse::success('completed', 'Cash payment processed successfully', [
                'payment_id' => $payment->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cash payment processing failed: ' . $e->getMessage());
            return PaymentResponse::failure($e->getMessage());
        }
    }

    public function verify(Payment $payment): PaymentResponse
    {
        if ($payment->status === 'completed') {
            return PaymentResponse::success('completed', 'Payment verified');
        }
        return PaymentResponse::failure('Payment not completed', 'pending');
    }

    public function cancel(Payment $payment): PaymentResponse
    {
        $payment->update([
            'status' => 'cancelled',
            'cancelled_at' => now()
        ]);
        return PaymentResponse::success('cancelled', 'Payment cancelled');
    }

    public function getRedirectResponse(Payment $payment): PaymentResponse
    {
        return PaymentResponse::failure('Cash payment does not support redirection');
    }
}
