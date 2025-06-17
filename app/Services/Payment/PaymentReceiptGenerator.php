<?php

namespace App\Services\Payment;

use App\Models\Payment;
use Illuminate\Support\Facades\Storage;
use PDF;

class PaymentReceiptGenerator
{
    public function generateReceipt(Payment $payment): string
    {
        $data = [
            'payment' => $payment,
            'user' => $payment->user,
            'method' => $payment->paymentMethod,
            'date' => $payment->processed_at->format('Y-m-d H:i:s'),
            'receipt_number' => 'RCP-' . str_pad($payment->id, 8, '0', STR_PAD_LEFT)
        ];

        $pdf = PDF::loadView('pdf.payment-receipt', $data);
        
        $filename = 'receipts/' . $data['receipt_number'] . '.pdf';
        Storage::put($filename, $pdf->output());

        return $filename;
    }

    public function sendReceiptEmail(Payment $payment, string $receiptPath): void
    {
        $user = $payment->user;
        
        \Mail::send('emails.payment-receipt', [
            'payment' => $payment,
            'receipt_number' => 'RCP-' . str_pad($payment->id, 8, '0', STR_PAD_LEFT)
        ], function ($message) use ($user, $payment, $receiptPath) {
            $message->to($user->email)
                   ->subject('Payment Receipt - ' . $payment->id)
                   ->attach(Storage::path($receiptPath));
        });
    }
} 