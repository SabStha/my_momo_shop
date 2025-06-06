<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPaymentController extends Controller
{
    public function index()
    {
        return view('admin.payment-manager');
    }

    public function transactions(Request $request)
    {
        $query = Payment::with(['user', 'order'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20);

        return view('admin.payment.transactions', compact('transactions'));
    }

    public function export()
    {
        $payments = Payment::with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->streamDownload(function () use ($payments) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            fputcsv($file, ['ID', 'User', 'Order ID', 'Amount', 'Payment Method', 'Status', 'Date']);
            
            // Add data
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->user->name,
                    $payment->order_id,
                    $payment->amount,
                    $payment->payment_method,
                    $payment->status,
                    $payment->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        }, 'payments.csv');
    }
} 