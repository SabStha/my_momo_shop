<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashDenomination;
use App\Models\CashDenominationChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashDenominationController extends Controller
{
    public function index()
    {
        $denominations = CashDenomination::orderBy('value', 'desc')->get();
        $totalCash = $denominations->sum(function ($denomination) {
            return $denomination->value * $denomination->quantity;
        });

        return view('admin.cash-denominations.index', compact('denominations', 'totalCash'));
    }

    public function update(Request $request, CashDenomination $denomination)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request, $denomination) {
            $previousQuantity = $denomination->quantity;
            $denomination->update(['quantity' => $request->quantity]);

            CashDenominationChange::create([
                'cash_denomination_id' => $denomination->id,
                'user_id' => auth()->id(),
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $request->quantity,
                'change_type' => $request->quantity > $previousQuantity ? 'add' : 'remove',
                'reason' => $request->reason,
            ]);
        });

        return redirect()->route('admin.cash-denominations.index')
            ->with('success', 'Cash denomination quantity updated successfully.');
    }

    public function history(CashDenomination $denomination)
    {
        $changes = $denomination->changes()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('admin.cash-denominations.history', compact('denomination', 'changes'));
    }

    public function getTotalCash()
    {
        $denominations = CashDenomination::orderBy('value', 'desc')->get();
        $totalCash = $denominations->sum(function ($denomination) {
            return $denomination->value * $denomination->quantity;
        });

        $denominationCounts = $denominations->pluck('quantity', 'value')->toArray();

        return response()->json([
            'total_cash' => $totalCash,
            'denominations' => $denominationCounts
        ]);
    }
}
