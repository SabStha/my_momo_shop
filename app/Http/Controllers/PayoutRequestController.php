<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayoutRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Creator;

class PayoutRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $creator = $user->creator;

        if (!$creator) {
            return redirect()->route('creator-dashboard.index')->with('error', 'You need to create a creator profile first.');
        }

        $payouts = PayoutRequest::where('creator_id', $creator->id)
            ->orderByDesc('requested_at')
            ->get();

        return view('creators.payouts', compact('payouts', 'creator'));
    }

    public function requestPayout(Request $request)
    {
        $user = Auth::user();
        $creator = $user->creator;

        if (!$creator) {
            return redirect()->route('creator-dashboard.index')->with('error', 'You need to create a creator profile first.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $creator->earnings,
            'payment_method' => 'required|in:bank_transfer,paypal,stripe',
            'payment_details' => 'required|array'
        ]);

        $payout = PayoutRequest::create([
            'creator_id' => $creator->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'payment_details' => $request->payment_details,
            'status' => 'pending',
            'requested_at' => now()
        ]);

        return redirect()->route('creator.payouts')->with('success', 'Payout request submitted successfully.');
    }
} 