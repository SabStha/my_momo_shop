<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\Branch;
use App\Models\CashDenomination;
use App\Models\CashDenominationChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        if (!$branchId) {
            return redirect()->route('admin.branches.index')
                ->with('error', 'Please select a branch first.');
        }

        $branch = Branch::findOrFail($branchId);
        $activeSession = Session::where('branch_id', $branchId)
            ->where('status', 'active')
            ->with(['openedBy'])
            ->first();

        $sessions = Session::where('branch_id', $branchId)
            ->with(['openedBy', 'closedBy'])
            ->latest()
            ->paginate(10);

        return view('admin.sessions.index', compact('branch', 'sessions', 'activeSession'));
    }

    public function show(Session $session)
    {
        $this->authorize('view', $session);

        $session->load(['openedBy', 'closedBy', 'orders' => function ($query) {
            $query->with(['customer', 'paymentMethod']);
        }]);

        return view('admin.sessions.show', compact('session'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'denominations' => 'required|array',
            'denominations.*' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $branchId = $request->input('branch_id') 
            ?? $request->query('branch') 
            ?? session('selected_branch_id') 
            ?? (auth()->user() ? auth()->user()->branch_id : null);

        if (!$branchId) {
            return redirect()->back()->with('error', 'No branch selected.');
        }

        DB::transaction(function () use ($request, $branchId) {
            // Create the session
            $session = Session::create([
                'branch_id' => $branchId,
                'opened_by' => auth()->id(),
                'opening_cash' => $request->opening_cash,
                'notes' => $request->notes,
                'opened_at' => now(),
                'status' => 'active'
            ]);

            // Update cash denominations
            foreach ($request->denominations as $value => $quantity) {
                $denomination = CashDenomination::where('value', $value)->first();
                if ($denomination) {
                    $denomination->update(['quantity' => $quantity]);
                    
                    // Log the change
                    CashDenominationChange::create([
                        'cash_denomination_id' => $denomination->id,
                        'user_id' => auth()->id(),
                        'previous_quantity' => $denomination->getOriginal('quantity'),
                        'new_quantity' => $quantity,
                        'change_type' => 'session_open',
                        'reason' => 'Session opened: ' . $session->id
                    ]);
                }
            }
        });

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Session opened successfully.');
    }

    public function close(Request $request, Session $session)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'denominations' => 'required|array',
            'denominations.*' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $session) {
            // Update the session
            $session->update([
                'closed_by' => auth()->id(),
                'closing_cash' => $request->closing_cash,
                'notes' => $request->notes,
                'closed_at' => now(),
                'status' => 'closed'
            ]);

            // Update cash denominations
            foreach ($request->denominations as $value => $quantity) {
                $denomination = CashDenomination::where('value', $value)->first();
                if ($denomination) {
                    $denomination->update(['quantity' => $quantity]);
                    
                    // Log the change
                    CashDenominationChange::create([
                        'cash_denomination_id' => $denomination->id,
                        'user_id' => auth()->id(),
                        'previous_quantity' => $denomination->getOriginal('quantity'),
                        'new_quantity' => $quantity,
                        'change_type' => 'session_close',
                        'reason' => 'Session closed: ' . $session->id
                    ]);
                }
            }
        });

        return redirect()->route('admin.sessions.index')
            ->with('success', 'Session closed successfully.');
    }
} 