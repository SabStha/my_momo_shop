<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayoutRequest;
use Carbon\Carbon;

class AdminPayoutController extends Controller
{
    public function index()
    {
        $payouts = PayoutRequest::with('creator.user')->orderBy('status')->orderByDesc('requested_at')->get();
        return view('admin.payouts', compact('payouts'));
    }

    public function approve($id)
    {
        $payout = PayoutRequest::findOrFail($id);
        $payout->status = 'approved';
        $payout->processed_at = Carbon::now();
        $payout->save();
        return redirect()->back()->with('success', 'Payout approved.');
    }

    public function reject($id)
    {
        $payout = PayoutRequest::findOrFail($id);
        $payout->status = 'rejected';
        $payout->processed_at = Carbon::now();
        $payout->save();
        return redirect()->back()->with('success', 'Payout rejected.');
    }
} 