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
        $recentPayments = Payment::with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_count' => Payment::count(),
            'today_count' => Payment::whereDate('created_at', today())->count(),
            'month_count' => Payment::whereMonth('created_at', now()->month)->count(),
            'total_amount' => Payment::sum('amount'),
            'today_amount' => Payment::whereDate('created_at', today())->sum('amount'),
            'month_amount' => Payment::whereMonth('created_at', now()->month)->sum('amount'),
        ];

        return view('desktop.admin.payment.index', compact('recentPayments', 'stats'));
    }

    public function transactions()
    {
        $transactions = Payment::with(['user', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('desktop.admin.payment.transactions', compact('transactions'));
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