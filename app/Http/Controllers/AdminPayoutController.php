<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Illuminate\Http\Request;

class AdminPayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::with(['creator.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.payouts.index', compact('payouts'));
    }

    public function approve(Payout $payout)
    {
        $payout->update([
            'status' => 'approved',
            'processed_at' => now()
        ]);

        return redirect()->route('admin.payouts.index')
            ->with('success', 'Payout has been approved.');
    }

    public function reject(Payout $payout)
    {
        $payout->update([
            'status' => 'rejected',
            'processed_at' => now()
        ]);

        return redirect()->route('admin.payouts.index')
            ->with('success', 'Payout has been rejected.');
    }

    public function markAsPaid(Payout $payout)
    {
        $payout->update([
            'status' => 'paid',
            'processed_at' => now()
        ]);

        return redirect()->route('admin.payouts.index')
            ->with('success', 'Payout has been marked as paid.');
    }
} 