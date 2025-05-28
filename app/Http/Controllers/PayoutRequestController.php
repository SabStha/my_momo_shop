<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayoutRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PayoutRequestController extends Controller
{
    public function index()
    {
        $creator = Auth::user()->creator;
        $payouts = PayoutRequest::where('creator_id', $creator->id)->orderByDesc('requested_at')->get();
        return view('creators.payouts', compact('payouts', 'creator'));
    }

    public function requestPayout(Request $request)
    {
        $creator = Auth::user()->creator;
        $amount = $creator->earnings;
        if ($amount <= 0) {
            return redirect()->back()->with('error', 'No earnings to payout.');
        }
        PayoutRequest::create([
            'creator_id' => $creator->id,
            'amount' => $amount,
            'status' => 'pending',
            'requested_at' => Carbon::now(),
        ]);
        $creator->earnings = 0;
        $creator->save();
        return redirect()->back()->with('success', 'Payout requested!');
    }
} 