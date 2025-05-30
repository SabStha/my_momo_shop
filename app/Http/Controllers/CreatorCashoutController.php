<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cashout;
use App\Models\Creator;
use Illuminate\Support\Facades\Auth;

class CreatorCashoutController extends Controller
{
    // Show creator's cashout history
    public function index()
    {
        $creator = Auth::user()->creator;
        $cashouts = Cashout::where('creator_id', $creator->id)->orderByDesc('created_at')->get();
        return view('creators.cashouts.index', compact('cashouts', 'creator'));
    }

    // Show form to request cashout
    public function create()
    {
        $creator = Auth::user()->creator;
        return view('creators.cashouts.create', compact('creator'));
    }

    // Validate & submit a new cashout request
    public function store(Request $request)
    {
        $creator = Auth::user()->creator;
        $minPoints = 100;
        $request->validate([
            'points' => 'required|integer|min:' . $minPoints,
            'amount' => 'required|integer|min:1',
        ]);
        if ($request->points > $creator->points) {
            return back()->withErrors(['points' => 'You do not have enough points.']);
        }
        // Optionally, calculate amount from points here
        Cashout::create([
            'creator_id' => $creator->id,
            'points' => $request->points,
            'amount' => $request->amount,
            'status' => 'pending',
        ]);
        // Optionally, deduct points from creator here
        return redirect()->route('creator.cashouts.index')->with('success', 'Cashout request submitted!');
    }

    // Admin: Show all requests with filter by status
    public function adminIndex(Request $request)
    {
        $status = $request->get('status');
        $query = Cashout::with('creator.user')->orderByDesc('created_at');
        if ($status) {
            $query->where('status', $status);
        }
        $cashouts = $query->get();
        return view('admin.cashouts.index', compact('cashouts', 'status'));
    }

    // Admin: Update status (approve/reject)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);
        $cashout = Cashout::findOrFail($id);
        $cashout->status = $request->status;
        $cashout->save();
        return redirect()->back()->with('success', 'Cashout status updated.');
    }
}
