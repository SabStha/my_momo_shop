<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->query('branch');
        $branch = Branch::findOrFail($branchId);

        $sessions = Session::where('branch_id', $branchId)
            ->with(['openedBy', 'closedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.sessions.index', compact('branch', 'sessions'));
    }

    public function open(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        // Check if there's already an active session
        $activeSession = Session::where('branch_id', $request->branch_id)
            ->where('status', 'active')
            ->first();

        if ($activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'There is already an active session for this branch.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $session = Session::create([
                'branch_id' => $request->branch_id,
                'opened_by' => Auth::id(),
                'opening_cash' => $request->opening_cash,
                'notes' => $request->notes,
                'status' => 'active',
                'opened_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Session opened successfully.',
                'session' => $session
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to open session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function close(Request $request, Session $session)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if (!$session->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'This session is not active.'
            ], 400);
        }

        if (!$session->canBeClosed()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot close session: There are pending orders.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Calculate final totals
            $session->calculateTotals();

            // Update session with closing details
            $session->update([
                'closed_by' => Auth::id(),
                'closing_cash' => $request->closing_cash,
                'notes' => $request->notes,
                'status' => 'closed',
                'closed_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Session closed successfully.',
                'session' => $session
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to close session: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show(Session $session)
    {
        $session->load(['branch', 'openedBy', 'closedBy', 'orders.payments.paymentMethod']);
        return view('admin.sessions.show', compact('session'));
    }
} 